/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { createMatcher } from '~~/test/matchers/utils'

export const toContainElement = createMatcher<Wrapper<any>>({
  name: 'toContainElement',
  test: (received, elementSelector) => received.find(elementSelector).exists(),
  passMessage: (_, elementSelector) => `Expected element to not contain '${elementSelector}'`,
  failMessage: (_, elementSelector) => `Expected element to contain '${elementSelector}'`,
  printReceived: false
})
