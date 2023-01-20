/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  homeVisitLongTermCare: 1,
  comprehensiveService: 2
} as const

/**
 * 介護保険サービス：予実区分.
 */
export type LtcsProvisionReportType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsProvisionReportType = createEnumerable($$, [
  [$$.homeVisitLongTermCare, '訪問介護'],
  [$$.comprehensiveService, '総合事業']
])

export const resolveLtcsProvisionReportType = LtcsProvisionReportType.resolve
