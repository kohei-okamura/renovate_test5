/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMatcher } from '~~/test/matchers/utils'

export const toBeTrue = createMatcher({
  name: 'toBeTrue',
  test: received => received === true,
  passMessage: () => 'Expected value to not be true',
  failMessage: () => 'Expected value to be true',
  printReceived: true
})
