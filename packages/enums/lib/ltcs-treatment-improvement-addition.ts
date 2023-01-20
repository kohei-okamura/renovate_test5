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
  addition2: 2,
  addition3: 3,
  addition4: 4,
  addition5: 5
} as const

/**
 * 介護保険サービス：介護職員処遇改善加算区分.
 */
export type LtcsTreatmentImprovementAddition = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsTreatmentImprovementAddition = createEnumerable($$, [
  [$$.none, 'なし'],
  [$$.addition1, '処遇改善加算（Ⅰ）'],
  [$$.addition2, '処遇改善加算（Ⅱ）'],
  [$$.addition3, '処遇改善加算（Ⅲ）'],
  [$$.addition4, '処遇改善加算（Ⅳ）'],
  [$$.addition5, '処遇改善加算（Ⅴ）']
])

export const resolveLtcsTreatmentImprovementAddition = LtcsTreatmentImprovementAddition.resolve
