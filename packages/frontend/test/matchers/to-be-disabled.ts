/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { createMatcher } from '~~/test/matchers/utils'

export const toBeDisabled = createMatcher<Wrapper<any>>({
  name: 'toBeDisabled',
  test: received => received.attributes('disabled') === 'disabled',
  passMessage: () => 'Expected element to not be disabled',
  failMessage: () => 'Expected element to be disabled',
  printReceived: false
})
