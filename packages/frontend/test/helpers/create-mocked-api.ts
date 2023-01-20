/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMock, Mocked } from '@zinger/helpers/testing/create-mock'
import { ApiService } from '~/services/api-service'

export function createMockedApi (...modules: (keyof ApiService)[]): Mocked<ApiService> {
  const xs = modules.map(module => [module, createMock()])
  const object = Object.fromEntries(xs)
  return createMock(object)
}
