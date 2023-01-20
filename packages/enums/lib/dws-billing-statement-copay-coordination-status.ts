/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  unapplicable: 11,
  unclaimable: 12,
  uncreated: 21,
  unfilled: 22,
  checking: 23,
  fulfilled: 31
} as const

/**
 * 障害福祉サービス：明細書：上限管理区分.
 */
export type DwsBillingStatementCopayCoordinationStatus = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingStatementCopayCoordinationStatus = createEnumerable($$, [
  [$$.unapplicable, '不要（上限管理なし）'],
  [$$.unclaimable, '不要（サービス提供なし）'],
  [$$.uncreated, '未作成'],
  [$$.unfilled, '未入力'],
  [$$.checking, '入力中'],
  [$$.fulfilled, '入力済']
])

export const resolveDwsBillingStatementCopayCoordinationStatus = DwsBillingStatementCopayCoordinationStatus.resolve
