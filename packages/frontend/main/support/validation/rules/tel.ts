/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { parsePhoneNumberFromString } from 'libphonenumber-js'
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'
import { VALID_FORMAT } from '~/models/phone-number-format'

/**
 * 独自バリデーションルール: 電話番号.
 */
export const tel: ValidationRuleSchema = {
  message: '有効な電話番号を入力してください。',
  validate: value => {
    const phone = parsePhoneNumberFromString(value, 'JP')
    return (phone?.isValid() ?? false) && VALID_FORMAT.test(value)
  }
}
