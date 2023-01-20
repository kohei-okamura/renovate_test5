/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, toRefs } from '@nuxtjs/composition-api'
import { NuxtContext } from '~/models/nuxt'
import { Refs } from '~/support/reactive'
import { RouteParams, RouteQuery } from '~/support/router/types'

export type RoutesServiceValues = {
  readonly path: string
  readonly fullPath: string
  readonly params: RouteParams
  readonly query: RouteQuery
}

export type RoutesService = Refs<RoutesServiceValues>

/**
 * Routes Service.
 *
 * ルーター関連のパラメーターをリアクティブに扱うためのユーティリティ・サービス.
 */
export function createRoutesService ({ app }: NuxtContext): RoutesService {
  const data = reactive({
    path: '',
    fullPath: '',
    params: {} as RouteParams,
    query: {} as RouteQuery
  })
  app.router!.afterEach(to => {
    data.path = to.path
    data.fullPath = to.fullPath
    data.params = to.params
    data.query = to.query
  })
  return toRefs(data)
}
