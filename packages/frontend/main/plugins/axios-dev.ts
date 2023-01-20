/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin } from '@nuxtjs/composition-api'
import { tap } from '@zinger/helpers'
import { stringifyQueryParams } from '~/support/utils/stringify-query-params'
import { stubAxios } from '~~/stubs/axios'

/**
 * リアクティブな値を含むオブジェクトを単純なオブジェクトに変換する.
 */
const sanitize = <T> (x: T): T => JSON.parse(JSON.stringify(x))

/**
 * Axios 開発用設定プラグイン.
 */
export default defineNuxtPlugin(({ $axios }) => {
  stubAxios($axios)
  $axios.interceptors.request.use(config => tap(config, () => {
    window.console.info(`${config.method} ${config.url}`, {
      data: sanitize(config.data ?? {}),
      params: sanitize(config.params ?? {}),
      query: stringifyQueryParams(config.params)
    })
  }))
})
