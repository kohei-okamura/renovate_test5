/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'
import { tel } from '~/support/validation/rules/tel'

/**
 * 独自バリデーションルール: FAX 番号.
 */
export const fax: ValidationRuleSchema = {
  ...tel,
  message: '有効なFAX番号を入力してください。'
}
