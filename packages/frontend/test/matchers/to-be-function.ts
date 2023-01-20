/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMatcher } from '~~/test/matchers/utils'

export const toBeFunction = createMatcher({
  name: 'toBeFunction',
  test: received => typeof received === 'function',
  passMessage: () => 'Expected value to not be a function',
  failMessage: () => 'Expected value to be a function',
  printReceived: true
})
