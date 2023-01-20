/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import VueRouter from 'vue-router'

export function createMockedRouter (): jest.Mocked<VueRouter> {
  const mock = createMock<VueRouter>({
    back: noop,
    push: noop,
    replace: noop
  })
  jest.spyOn(mock, 'back').mockReturnValue()
  jest.spyOn(mock, 'push').mockImplementation(() => Promise.resolve({}))
  jest.spyOn(mock, 'replace').mockImplementation(() => Promise.resolve({}))
  return mock
}
