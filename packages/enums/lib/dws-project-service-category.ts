/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  physicalCare: 11,
  housework: 12,
  accompanyWithPhysicalCare: 13,
  accompany: 14,
  visitingCareForPwsd: 21,
  ownExpense: 91
} as const

/**
 * 障害福祉サービス：計画：サービス区分.
 */
export type DwsProjectServiceCategory = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsProjectServiceCategory = createEnumerable($$, [
  [$$.physicalCare, '居宅：身体介護'],
  [$$.housework, '居宅：家事援助'],
  [$$.accompanyWithPhysicalCare, '居宅：通院等介助（身体を伴う）'],
  [$$.accompany, '居宅：通院等介助（身体を伴わない）'],
  [$$.visitingCareForPwsd, '重度訪問介護'],
  [$$.ownExpense, '自費']
])

export const resolveDwsProjectServiceCategory = DwsProjectServiceCategory.resolve
