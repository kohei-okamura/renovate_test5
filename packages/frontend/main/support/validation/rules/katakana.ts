/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'
import { HIRAGANA_CHARS, isOnly, KANA_COMMON_CAHRS, KATAKANA_CHARS } from '~/support/jaco'

/**
 * 独自バリデーションルール: カタカナ.
 */
export const katakana: ValidationRuleSchema = {
  message: 'カタカナで入力してください。',
  validate: value => isOnly(value, HIRAGANA_CHARS + KATAKANA_CHARS + KANA_COMMON_CAHRS)
}
