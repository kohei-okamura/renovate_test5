/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'

export type CustomRuleParams = {
  message: string
  validate: (value: any) => boolean
}

/**
 * 独自バリデーションルール: カスタムルール.
 */
export const custom: ValidationRuleSchema = {
  message: (_, { params }: Record<string, any> = {}) => (params as CustomRuleParams).message,
  params: ['params'],
  validate: (value, { params }: Record<string, any>) => (params as CustomRuleParams).validate(value)
}
