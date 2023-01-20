/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  physicalCare: 11,
  accompanyWithPhysicalCare: 12,
  housework: 13,
  accompany: 14,
  accessibleTaxi: 15,
  visitingCareForPwsd: 21,
  outingSupportForPwsd: 22
} as const

/**
 * サービス提供実績記録票：合計区分グループ.
 */
export type DwsBillingServiceReportAggregateGroup = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingServiceReportAggregateGroup = createEnumerable($$, [
  [$$.physicalCare, '居宅介護：合計1「身体介護」'],
  [$$.accompanyWithPhysicalCare, '居宅介護：合計2「通院等介助（身体を伴う）」'],
  [$$.housework, '居宅介護：合計3「家事援助」'],
  [$$.accompany, '居宅介護：合計4「通院等介助（身体を伴わない）」'],
  [$$.accessibleTaxi, '居宅介護：合計5「通院等乗降介助」'],
  [$$.visitingCareForPwsd, '重度訪問介護'],
  [$$.outingSupportForPwsd, '重度訪問介護：移動介護分']
])

export const resolveDwsBillingServiceReportAggregateGroup = DwsBillingServiceReportAggregateGroup.resolve
