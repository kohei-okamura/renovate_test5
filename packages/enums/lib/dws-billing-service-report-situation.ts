/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  hospitalized: 1,
  longHospitalized: 2
} as const

/**
 * サービス提供実績記録票：サービス提供の状況.
 */
export type DwsBillingServiceReportSituation = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingServiceReportSituation = createEnumerable($$, [
  [$$.none, '未設定'],
  [$$.hospitalized, '入院'],
  [$$.longHospitalized, '入院（長期）']
])

export const resolveDwsBillingServiceReportSituation = DwsBillingServiceReportSituation.resolve
