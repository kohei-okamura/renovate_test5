/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DirectiveFunction } from 'vue'
import { DirectiveBinding } from 'vue/types/options'
import { createEvent, findInputElement, getInputElement } from '~/directives/utils'

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
  const base = input.value ?? ''
  const kana = base.replace(/[！-～]/g, x => String.fromCharCode(x.charCodeAt(0) - 0xFEE0))
  if (base !== kana) {
    input.value = kana
    input.dispatchEvent(createEvent('input'))
  }
}

export const autoAscii: DirectiveFunction = (element, binding) => {
  const options = getOptions(binding)
  const input = findInputElement(element)
  input.addEventListener(options.event ?? 'blur', listener)
}
