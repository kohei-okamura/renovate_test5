/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateTime } from 'luxon'
import { RuleParamSchema, ValidationRuleSchema } from 'vee-validate/dist/types/types'
import { DateLike, READABLE_DATE_FORMAT } from '~/models/date'

type Params = Record<string, any>
type Cast = {
  (value: undefined): undefined
  (value: string | DateTime): DateTime
}

const cast: Cast = (value: any) => {
  if (value === undefined || DateTime.isDateTime(value)) {
    return value
  } else if (typeof value === 'string') {
    return DateTime.fromISO(value)
  } else {
    throw new TypeError(`invalid minValue: ${value}`)
  }
}
const minValue: RuleParamSchema = {
  name: 'minValue',
  cast
}
const attribute: RuleParamSchema = {
  name: 'attribute',
  default: undefined
}
const message = (minValue: DateTime, attribute: string | undefined): string => {
  return `${attribute ?? minValue.toFormat(READABLE_DATE_FORMAT)}以降の日付を入力してください。`
}
const validate = (value: DateLike, minValue: DateTime | undefined): boolean => {
  return value === undefined || minValue === undefined || cast(value) >= minValue
}

/**
 * 独自バリデーションルール: 最小年月日.
 */
export const minDate: ValidationRuleSchema = {
  message: (_, { minValue, attribute }: Params) => message(minValue, attribute),
  params: [minValue, attribute],
  validate: (value, { minValue }: Params) => validate(value, minValue)
}
