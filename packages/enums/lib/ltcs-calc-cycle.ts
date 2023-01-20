/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  perService: 1,
  perDay: 2,
  perMonth: 3
} as const

/**
 * 介護保険サービス：算定単位.
 */
export type LtcsCalcCycle = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsCalcCycle = createEnumerable($$, [
  [$$.perService, '1回につき'],
  [$$.perDay, '1日につき'],
  [$$.perMonth, '1月につき']
])

export const resolveLtcsCalcCycle = LtcsCalcCycle.resolve
