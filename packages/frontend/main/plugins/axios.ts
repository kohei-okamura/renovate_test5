/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin } from '@nuxtjs/composition-api'
import { AxiosResponse } from 'axios'
import { option, Option } from 'ts-option'
import { errors } from '~/models/errors'
import { HttpStatusCode } from '~/models/http-status-code'
import { removeNull } from '~/support/utils/remove-null'
import { stringifyQueryParams } from '~/support/utils/stringify-query-params'

export type Cause = any

export interface AxiosErrorWrapper {
  onBadRequest (f: (errors: Dictionary<string[]>) => void): AxiosErrorWrapper
  onError (f: (cause: Cause, statusCode?: number) => void): AxiosErrorWrapper
  onForbidden (f: (cause: Cause) => void): AxiosErrorWrapper
  onNotFound (f: (cause: Cause) => void): AxiosErrorWrapper
  onUnauthorized (f: (cause: Cause) => void): AxiosErrorWrapper
}

class AxiosErrorWrapperImpl implements AxiosErrorWrapper {
  private readonly response: Option<AxiosResponse>
  private readonly statusCode?: number

  constructor (private cause: Cause) {
    this.response = option(cause).flatMap<AxiosResponse>(x => option(x.response))
    this.statusCode = this.response.flatMap(x => option(x.status)).orNull ?? undefined
  }

  onBadRequest (f: (errors: Dictionary<string[]>) => void): AxiosErrorWrapper {
    if (this.statusCode === HttpStatusCode.BadRequest) {
      const errors = this.response
        .flatMap(response => option(response.data))
        .flatMap(data => option(data.errors))
        .getOrElse(() => ({}))
      f(errors)
    }
    return this
  }

  onError (f: (cause: Cause, statusCode?: number) => void): AxiosErrorWrapper {
    f(this.cause, this.statusCode)
    return this
  }

  onForbidden (f: (cause: Cause) => void): AxiosErrorWrapper {
    return this.onGenericError(HttpStatusCode.Forbidden, f)
  }

  onNotFound (f: (cause: Cause) => void): AxiosErrorWrapper {
    return this.onGenericError(HttpStatusCode.NotFound, f)
  }

  onUnauthorized (f: (cause: Cause) => void): AxiosErrorWrapper {
    return this.onGenericError(HttpStatusCode.Unauthorized, f)
  }

  private onGenericError (statusCode: number, f: (cause: Cause) => void): AxiosErrorWrapper {
    if (this.statusCode === statusCode) {
      f(this.cause)
    }
    return this
  }
}

export interface WrapAxiosErrorFunction {
  (cause: Cause): AxiosErrorWrapper
}

export function $wrapAxiosError (cause: Cause): AxiosErrorWrapper {
  return new AxiosErrorWrapperImpl(cause)
}

export default defineNuxtPlugin(({ $axios, error, redirect, app }, inject) => {
  $axios.defaults.paramsSerializer = stringifyQueryParams
  $axios.onResponse(response => {
    // レスポンスから値が null のデータを取り除く
    response.data = removeNull(response.data)
  })
  $axios.onError(reason => {
    const status = reason.response?.status
    const url = reason.response?.config.url
    const passthroughErrors = reason.response?.config.passthroughErrors
    if (status === HttpStatusCode.NotFound && !passthroughErrors) {
      if (url?.startsWith('/')) {
        error(errors.notFound)
      }
    } else if (status === HttpStatusCode.Unauthorized) {
      app.$globalStore.session.deleteAuth()
      // セッション取得の 401（= 未ログイン）はリダイレクトしない
      if (!url?.includes('/sessions/my')) {
        if (app.$routes.path.value !== '/') {
          redirect(`/?path=${app.$routes.fullPath.value}`)
        }
      }
    } else if (status !== HttpStatusCode.BadRequest && !passthroughErrors) {
      app.$alert.error('エラーが発生しました、管理者へのお問い合わせをお願いします。', reason.stack)
    }
  })
  inject('wrapAxiosError', (cause: Cause) => {
    return $wrapAxiosError(cause)
  })
})
