/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMatcher } from '~~/test/matchers/utils'

export const toBeEmptyArray = createMatcher({
  name: 'toBeEmptyArray',
  test: received => Array.isArray(received) && received.length === 0,
  passMessage: () => 'Expected value to not be an empty array',
  failMessage: () => 'Expected value to be an empty array',
  printReceived: true
})
