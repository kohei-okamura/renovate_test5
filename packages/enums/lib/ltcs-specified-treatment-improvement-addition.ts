/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  addition1: 1,
  addition2: 2
} as const

/**
 * 介護保険サービス：介護職員等特定処遇改善加算区分.
 */
export type LtcsSpecifiedTreatmentImprovementAddition = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsSpecifiedTreatmentImprovementAddition = createEnumerable($$, [
  [$$.none, 'なし'],
  [$$.addition1, '特定処遇改善加算（Ⅰ）'],
  [$$.addition2, '特定処遇改善加算（Ⅱ）']
])

export const resolveLtcsSpecifiedTreatmentImprovementAddition = LtcsSpecifiedTreatmentImprovementAddition.resolve
