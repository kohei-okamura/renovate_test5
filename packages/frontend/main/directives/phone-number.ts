/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { parsePhoneNumberFromString } from 'libphonenumber-js'
import { DirectiveFunction } from 'vue'
import { DirectiveBinding } from 'vue/types/options'
import { createEvent, findInputElement, getInputElement } from '~/directives/utils'
import { VALID_FORMAT } from '~/models/phone-number-format'

type Options = {
  event?: string
}

type Value = boolean | undefined | Options

function getOptions (binding: DirectiveBinding): Options {
  const value: Value = binding.value
  return typeof value === 'boolean' ? {} : (value ?? {})
}

function listener (event: Event) {
  const input = getInputElement(event)
  const base = input.value.trim()
  const phone = parsePhoneNumberFromString(base, 'JP')
  if (phone?.isValid() && VALID_FORMAT.test(base)) {
    const formatted = phone.formatNational()
    if (base !== formatted) {
      input.value = formatted
      input.dispatchEvent(createEvent('input'))
    }
  }
}

export const phoneNumber: DirectiveFunction = (element, binding) => {
  const options = getOptions(binding)
  const input = findInputElement(element)
  input.addEventListener(options.event ?? 'blur', listener)
}
