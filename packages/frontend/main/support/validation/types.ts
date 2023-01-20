/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationObserver, ValidationProvider } from 'vee-validate'
import { DateLike } from '~/models/date'
import { CustomRuleParams } from '~/support/validation/rules/custom'

type BetweenRule = {
  min: number
  max: number
}

type BooleanRule = boolean

type CustomRule = CustomRuleParams

type MinDateRule =
  (DateLike | undefined) |
  [DateLike | undefined] |
  [DateLike | undefined, string] |
  {
    minValue: DateLike | undefined
    attribute?: string
  }

type LengthRule = number | {
  len: number
  max?: number
}

type NumericRule = number | string

type NonItemsZeroRule = {
  itemName: string
}

export type Rule = {
  asciiAlphaNum?: BooleanRule
  alphaNum?: BooleanRule
  between?: BetweenRule
  custom?: CustomRule
  digits?: NumericRule
  email?: BooleanRule
  fax?: BooleanRule
  hiragana?: BooleanRule
  length?: LengthRule
  katakana?: BooleanRule
  max?: NumericRule
  min?: NumericRule
  minDate?: MinDateRule
  minValue?: NumericRule
  nonItemsZero?: NonItemsZeroRule
  integer?: BooleanRule
  numeric?: BooleanRule
  postcode?: BooleanRule
  required?: BooleanRule
  tel?: BooleanRule
  validTimeDuration?: BooleanRule
  zenginDataRecordChar?: BooleanRule
}

export type Rules = {
  [key: string]: Rule | Rule[] | Rules | Rules[] | undefined
}

export type ValidationObserverInstance = InstanceType<typeof ValidationObserver>

export type ValidationProviderInstance = InstanceType<typeof ValidationProvider>
