/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMatcher } from '~~/test/matchers/utils'

export const toBeArray = createMatcher({
  name: 'toBeArray',
  test: received => Array.isArray(received),
  passMessage: () => 'Expected value to not be an array',
  failMessage: () => 'Expected value to be an array',
  printReceived: true
})
