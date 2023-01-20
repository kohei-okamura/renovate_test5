/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  score: 11,
  baseScore: 12,
  percent: 21,
  permille: 22
} as const

/**
 * 介護保険サービス：単位数区分.
 */
export type LtcsCalcType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsCalcType = createEnumerable($$, [
  [$$.score, '単位数'],
  [$$.baseScore, 'きざみ基準単位数'],
  [$$.percent, '%値'],
  [$$.permille, '1/1000値']
])

export const resolveLtcsCalcType = LtcsCalcType.resolve
