/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { createMatcher } from '~~/test/matchers/utils'

export const toExist = createMatcher<Wrapper<any>>({
  name: 'toExist',
  test: received => received.exists(),
  passMessage: () => 'Expected component (or element) to not exist',
  failMessage: () => 'Expected component (or element) to exist',
  printReceived: false
})
