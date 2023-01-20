/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'

/**
 * 独自バリデーションルール: １つ以上必要な要素.
 */
export const nonItemsZero: ValidationRuleSchema = {
  validate: value => !isNaN(value) && value > 0,
  params: ['itemName', 'action'],
  message: (_, params) => `${params.itemName}を1つ以上${params.action ?? '追加'}してください。`
}
