/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ComputedRef } from '@nuxtjs/composition-api'
import { isFunction } from '@zinger/helpers'
import { Rules } from '~/support/validation/types'

type ValidationRules = {
  (rules: Rules): Rules
  (rules: () => Rules): ComputedRef<Rules>
}

export const validationRules: ValidationRules = (rules: Rules | (() => Rules)): any => {
  return isFunction(rules) ? computed(rules) : rules
}
