/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  withdrawal: 1,
  transfer: 2,
  collection: 3
} as const

/**
 * 支払方法.
 */
export type PaymentMethod = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const PaymentMethod = createEnumerable($$, [
  [$$.none, '未設定'],
  [$$.withdrawal, '口座振替'],
  [$$.transfer, '銀行振込'],
  [$$.collection, '集金']
])

export const resolvePaymentMethod = PaymentMethod.resolve
