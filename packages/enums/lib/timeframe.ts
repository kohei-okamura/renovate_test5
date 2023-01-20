/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  daytime: 1,
  morning: 2,
  night: 3,
  midnight: 4,
  unknown: 9
} as const

/**
 * 時間帯.
 */
export type Timeframe = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Timeframe = createEnumerable($$, [
  [$$.daytime, '日中'],
  [$$.morning, '早朝'],
  [$$.night, '夜間'],
  [$$.midnight, '深夜'],
  [$$.unknown, '未定義']
])

export const resolveTimeframe = Timeframe.resolve
