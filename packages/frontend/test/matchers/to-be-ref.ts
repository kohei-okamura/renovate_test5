/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { isRef } from '@nuxtjs/composition-api'
import { createMatcher } from '~~/test/matchers/utils'

export const toBeRef = createMatcher({
  name: 'toBeRef',
  test: received => isRef(received),
  passMessage: () => 'Expected value to not be a Ref',
  failMessage: () => 'Expected value to be a Ref',
  printReceived: true
})
