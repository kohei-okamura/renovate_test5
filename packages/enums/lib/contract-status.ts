/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  provisional: 1,
  formal: 2,
  terminated: 3,
  disabled: 9
} as const

/**
 * 契約：状態.
 */
export type ContractStatus = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const ContractStatus = createEnumerable($$, [
  [$$.provisional, '仮契約'],
  [$$.formal, '本契約'],
  [$$.terminated, '契約終了'],
  [$$.disabled, '無効']
])

export const resolveContractStatus = ContractStatus.resolve
