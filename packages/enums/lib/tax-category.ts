/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  unapplicable: 0,
  consumptionTax: 1,
  reducedConsumptionTax: 2
} as const

/**
 * 税率区分.
 */
export type TaxCategory = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const TaxCategory = createEnumerable($$, [
  [$$.unapplicable, '該当なし'],
  [$$.consumptionTax, '消費税'],
  [$$.reducedConsumptionTax, '消費税（軽減税率）']
])

export const resolveTaxCategory = TaxCategory.resolve
