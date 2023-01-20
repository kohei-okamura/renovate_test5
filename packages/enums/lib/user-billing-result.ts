/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  pending: 0,
  inProgress: 1,
  paid: 2,
  unpaid: 3,
  none: 4
} as const

/**
 * 利用者請求：請求結果.
 */
export type UserBillingResult = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const UserBillingResult = createEnumerable($$, [
  [$$.pending, '未処理'],
  [$$.inProgress, '処理中'],
  [$$.paid, '入金済'],
  [$$.unpaid, '口座振替未済'],
  [$$.none, '請求なし']
])

export const resolveUserBillingResult = UserBillingResult.resolve
