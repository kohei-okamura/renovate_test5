/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMatcher } from '~~/test/matchers/utils'

export const toBeFalse = createMatcher({
  name: 'toBeFalse',
  test: received => received === false,
  passMessage: () => 'Expected value to not be false',
  failMessage: () => 'Expected value to be false',
  printReceived: true
})
