/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'

/**
 * 独自バリデーションルール: 郵便番号.
 */
export const postcode: ValidationRuleSchema = {
  message: '郵便番号は7桁で入力してください。',
  validate: value => /^(|\d{3}-\d{4})$/.test(value)
}
