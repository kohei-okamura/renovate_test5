/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, toRefs } from '@nuxtjs/composition-api'
import { RoutesService, RoutesServiceValues } from '~/services/routes-service'

export function createMockedRoutes (params: Partial<RoutesServiceValues>): RoutesService {
  const x = reactive({
    path: '/',
    fullPath: '/',
    params: {},
    query: {},
    ...params
  })
  return toRefs(x)
}
