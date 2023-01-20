/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  category100: 1,
  category90: 2,
  category70: 3,
  categoryPwsd: 4,
  categoryTotal: 5
} as const

/**
 * サービス提供実績記録票：合計区分カテゴリー.
 */
export type DwsBillingServiceReportAggregateCategory = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingServiceReportAggregateCategory = createEnumerable($$, [
  [$$.category100, '内訳 100%'],
  [$$.category90, '内訳 90%'],
  [$$.category70, '内訳 70%'],
  [$$.categoryPwsd, '内訳 重訪'],
  [$$.categoryTotal, '合計 算定時間数計']
])

export const resolveDwsBillingServiceReportAggregateCategory = DwsBillingServiceReportAggregateCategory.resolve
