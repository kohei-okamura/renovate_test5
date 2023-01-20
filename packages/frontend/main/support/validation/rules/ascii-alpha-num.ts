/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'

/**
 * 独自バリデーションルール: 半角英数字.
 */
export const asciiAlphaNum: ValidationRuleSchema = {
  message: '半角英数字で入力してください。',
  validate: value => /^[A-Za-z\d]*$/.test(value)
}
