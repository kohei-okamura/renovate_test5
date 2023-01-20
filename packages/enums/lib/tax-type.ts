/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  taxExcluded: 1,
  taxIncluded: 2,
  taxExempted: 3
} as const

/**
 * 課税区分.
 */
export type TaxType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const TaxType = createEnumerable($$, [
  [$$.taxExcluded, '税抜'],
  [$$.taxIncluded, '税込'],
  [$$.taxExempted, '非課税']
])

export const resolveTaxType = TaxType.resolve
