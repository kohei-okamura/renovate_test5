/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  homeHelpService: '0101',
  visitingCareForPwsd: '0301'
} as const

/**
 * サービス提供実績記録票：様式種別番号.
 */
export type DwsBillingServiceReportFormat = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingServiceReportFormat = createEnumerable($$, [
  [$$.homeHelpService, '様式1（居宅介護サービス提供実績記録票情報）'],
  [$$.visitingCareForPwsd, '様式3-1（重度訪問介護サービス提供実績記録票）']
])

export const resolveDwsBillingServiceReportFormat = DwsBillingServiceReportFormat.resolve
