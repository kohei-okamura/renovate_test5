/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  unknown: 0,
  ordinaryDeposit: 1,
  currentDeposit: 2,
  fixedDeposit: 3
} as const

/**
 * 銀行口座：種別.
 */
export type BankAccountType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const BankAccountType = createEnumerable($$, [
  [$$.unknown, '不明'],
  [$$.ordinaryDeposit, '普通預金'],
  [$$.currentDeposit, '当座預金'],
  [$$.fixedDeposit, '定期預金']
])

export const resolveBankAccountType = BankAccountType.resolve
