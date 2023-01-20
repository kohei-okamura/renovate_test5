/* eslint-disable camelcase */
/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { configure, extend, localize, ValidationObserver, ValidationProvider } from 'vee-validate'
import {
  alpha_num as alphaNum,
  between,
  digits,
  email,
  integer,
  length,
  max,
  max_value as maxValue,
  min,
  min_value as minValue,
  numeric,
  required
} from 'vee-validate/dist/rules'
import { VueConstructor } from 'vue'
import { asciiAlphaNum } from '~/support/validation/rules/ascii-alpha-num'
import { custom } from '~/support/validation/rules/custom'
import { fax } from '~/support/validation/rules/fax'
import { hiragana } from '~/support/validation/rules/hiragana'
import { katakana } from '~/support/validation/rules/katakana'
import { minDate } from '~/support/validation/rules/min-date'
import { nonItemsZero } from '~/support/validation/rules/non-items-zero'
import { postcode } from '~/support/validation/rules/postcode'
import { tel } from '~/support/validation/rules/tel'
import { validTimeDuration } from '~/support/validation/rules/valid-time-durations'
import { zenginDataRecordChar } from '~/support/validation/rules/zengin-data-record-char'

function setupComponents (vue: VueConstructor): void {
  vue.component('ValidationObserver', ValidationObserver)
  vue.component('ValidationProvider', ValidationProvider)
}

function setupRules (): void {
  extend('alphaNum', alphaNum)
  extend('asciiAlphaNum', asciiAlphaNum)
  extend('between', between)
  extend('custom', custom)
  extend('digits', digits)
  extend('email', email)
  extend('fax', fax)
  extend('hiragana', hiragana)
  extend('integer', integer)
  extend('katakana', katakana)
  extend('length', length)
  extend('max', max)
  extend('maxValue', maxValue)
  extend('min', min)
  extend('minDate', minDate)
  extend('minValue', minValue)
  extend('nonItemsZero', nonItemsZero)
  extend('numeric', numeric)
  extend('postcode', postcode)
  extend('required', required)
  extend('tel', tel)
  extend('validTimeDuration', validTimeDuration)
  extend('zenginDataRecordChar', zenginDataRecordChar)
}

function setupMessages (): void {
  localize('ja', {
    messages: {
      alphaNum: '半角英数字で入力してください',
      between: (_, params = {}) => `${params.min}以上、${params.max}以下の半角数字で入力してください。`,
      digits: (_, params = {}) => `${params.length}桁の半角数字で入力してください。`,
      email: '有効なメールアドレスを入力してください。',
      integer: '半角数字のみで入力してください。',
      length: (_, params = {}) => `${params.length}文字で入力してください。`,
      max: (_, params = {}) => `${params.length}文字以内で入力してください。`,
      maxValue: (_, params = {}) => `${params.max}以下を入力してください。`,
      min: (_, params = {}) => `${params.length}文字以上で入力してください。`,
      minValue: (_, params = {}) => `${params.min}以上を入力してください。`,
      numeric: '半角数字のみで入力してください。',
      required: '入力してください。'
    }
  })
}

/**
 * VeeValidate 関連のセットアップ処理.
 */
export function setupVeeValidate (vue: VueConstructor): void {
  configure({
    mode: 'eager'
  })
  setupComponents(vue)
  setupRules()
  setupMessages()
}
