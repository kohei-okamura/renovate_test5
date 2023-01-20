/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  zero: 0,
  eight: 8,
  ten: 10
} as const

/**
 * 消費税.
 */
export type ConsumptionTaxRate = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const ConsumptionTaxRate = createEnumerable($$, [
  [$$.zero, '0%'],
  [$$.eight, '8%'],
  [$$.ten, '10%']
])

export const resolveConsumptionTaxRate = ConsumptionTaxRate.resolve
