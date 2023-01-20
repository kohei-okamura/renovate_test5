/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'
import { TimeDuration } from '~/models/time-duration'

/**
 * 独自バリデーションルール: 有効なTimeDurationであることを検証する.
 */
export const validTimeDuration: ValidationRuleSchema = {
  message: () => '半角数字を入力してください。',
  validate: value => typeof value === 'number' || (TimeDuration.isTimeDuration(value) && value.isValid)
}
