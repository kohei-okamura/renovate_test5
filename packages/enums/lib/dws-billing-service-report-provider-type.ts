/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  novice: 11,
  beginner: 12,
  careWorkerForPwsd: 13
} as const

/**
 * サービス提供実績記録票：ヘルパー資格.
 */
export type DwsBillingServiceReportProviderType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingServiceReportProviderType = createEnumerable($$, [
  [$$.none, '未設定'],
  [$$.novice, '初任者等'],
  [$$.beginner, '基礎等'],
  [$$.careWorkerForPwsd, '重訪']
])

export const resolveDwsBillingServiceReportProviderType = DwsBillingServiceReportProviderType.resolve
