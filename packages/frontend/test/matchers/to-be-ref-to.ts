/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { isRef } from '@nuxtjs/composition-api'
import { createMatcher } from '~~/test/matchers/utils'

export const toBeRefTo = createMatcher({
  name: 'toBeRefTo',
  test: (received, value) => isRef(received) && received.value === value,
  passMessage: (_, value) => `Expected value to not be a Ref to ${value}`,
  failMessage: (_, value) => `Expected value to be a Ref to ${value}`,
  printReceived: true
})
