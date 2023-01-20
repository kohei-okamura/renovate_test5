/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  lastWeek: 1,
  thisWeek: 2,
  nextWeek: 3,
  lastMonth: 4,
  thisMonth: 5,
  nextMonth: 6,
  specify: 7
} as const

/**
 * 日付範囲区分.
 */
export type DateRangeType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DateRangeType = createEnumerable($$, [
  [$$.lastWeek, '先週'],
  [$$.thisWeek, '今週'],
  [$$.nextWeek, '来週'],
  [$$.lastMonth, '先月'],
  [$$.thisMonth, '今月'],
  [$$.nextMonth, '来月'],
  [$$.specify, '範囲を指定']
])

export const resolveDateRangeType = DateRangeType.resolve
