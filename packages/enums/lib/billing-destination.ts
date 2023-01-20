/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  theirself: 1,
  agent: 2,
  corporation: 3
} as const

/**
 * 請求先.
 */
export type BillingDestination = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const BillingDestination = createEnumerable($$, [
  [$$.none, '未設定'],
  [$$.theirself, '本人'],
  [$$.agent, '本人以外（個人）'],
  [$$.corporation, '本人以外（法人・団体）']
])

export const resolveBillingDestination = BillingDestination.resolve
