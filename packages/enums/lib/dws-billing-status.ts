/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  checking: 10,
  ready: 20,
  fixed: 30,
  disabled: 99
} as const

/**
 * 障害福祉サービス：請求：状態.
 */
export type DwsBillingStatus = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingStatus = createEnumerable($$, [
  [$$.checking, '入力中'],
  [$$.ready, '未確定'],
  [$$.fixed, '確定済'],
  [$$.disabled, '無効']
])

export const resolveDwsBillingStatus = DwsBillingStatus.resolve
