/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  done: 0,
  shortage: 1,
  noAccount: 2,
  depositorCause: 3,
  noRequest: 4,
  bankingClientCause: 8,
  other: 9,
  pending: 99
} as const

/**
 * 利用者請求：振替結果コード.
 */
export type WithdrawalResultCode = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const WithdrawalResultCode = createEnumerable($$, [
  [$$.done, '振替済'],
  [$$.shortage, '資金不足'],
  [$$.noAccount, '取引なし'],
  [$$.depositorCause, '預金者都合'],
  [$$.noRequest, '依頼書なし'],
  [$$.bankingClientCause, '委託者都合'],
  [$$.other, 'その他'],
  [$$.pending, '未処理']
])

export const resolveWithdrawalResultCode = WithdrawalResultCode.resolve
