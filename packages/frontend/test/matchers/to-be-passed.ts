/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationObserverInstance } from '~/support/validation/types'
import { createMatcher } from '~~/test/matchers/utils'

type VeeValidateErrors = {
  [key: string]: string[] | VeeValidateErrors
}

function flattenValidationErrors (errors: VeeValidateErrors): Dictionary<string[]> {
  const xs = Object.keys(errors).map(key => {
    const x = errors[key]
    if (Array.isArray(x)) {
      return x.length === 0 ? {} : { [key]: x }
    } else {
      return flattenValidationErrors(x)
    }
  })
  return Object.assign({}, ...xs)
}

function serializeValidationErrors (errors: VeeValidateErrors): string {
  return JSON.stringify(flattenValidationErrors(errors), null, '  ')
}

export const toBePassed = createMatcher<ValidationObserverInstance>({
  name: 'toBePassed',
  test: received => received.flags.passed,
  passMessage: () => 'Expected validation not to be passed, but it is passed',
  failMessage: received => `Expected validation to be passed received:\n  ${serializeValidationErrors(received.errors)}`,
  printReceived: false
})
