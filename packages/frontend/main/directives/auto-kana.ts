/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DirectiveFunction } from 'vue'
import { DirectiveBinding } from 'vue/types/options'
import { createEvent, findInputElement, getInputElement } from '~/directives/utils'
import { toKatakana } from '~/support/jaco'

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
  const kana = toKatakana(base)
  if (base !== kana) {
    input.value = kana
    input.dispatchEvent(createEvent('input'))
  }
}

export const autoKana: DirectiveFunction = (element, binding) => {
  const options = getOptions(binding)
  const input = findInputElement(element)
  input.addEventListener(options.event ?? 'blur', listener)
}
