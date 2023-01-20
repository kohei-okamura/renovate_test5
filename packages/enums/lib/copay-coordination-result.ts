/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  appropriated: 1,
  notCoordinated: 2,
  coordinated: 3
} as const

/**
 * 上限管理結果.
 */
export type CopayCoordinationResult = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const CopayCoordinationResult = createEnumerable($$, [
  [$$.appropriated, '1. 管理事業所で利用者負担額を充当したため、他事業所の利用者負担は発生しない。'],
  [$$.notCoordinated, '2. 利用者負担額の合計額が、負担上限月額以下のため、調整事務は行わない。'],
  [$$.coordinated, '3. 利用者負担額の合計額が、負担上限月額を超過するため、下記のとおり調整した。']
])

export const resolveCopayCoordinationResult = CopayCoordinationResult.resolve
