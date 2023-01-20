/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMock } from '@zinger/helpers/testing/create-mock'
import { Route } from 'vue-router'

export const createMockedRoute = (route: Partial<Route> = {}): jest.Mocked<Route> => createMock<Route>({
  params: {},
  query: {},
  ...route
})
