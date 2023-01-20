/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  everyWeek: 11,
  oddWeek: 12,
  evenWeek: 13,
  firstWeekOfMonth: 21,
  secondWeekOfMonth: 22,
  thirdWeekOfMonth: 23,
  fourthWeekOfMonth: 24,
  lastWeekOfMonth: 25
} as const

/**
 * 繰り返し周期.
 */
export type Recurrence = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Recurrence = createEnumerable($$, [
  [$$.everyWeek, '毎週'],
  [$$.oddWeek, '奇数週'],
  [$$.evenWeek, '偶数週'],
  [$$.firstWeekOfMonth, '第1週'],
  [$$.secondWeekOfMonth, '第2週'],
  [$$.thirdWeekOfMonth, '第3週'],
  [$$.fourthWeekOfMonth, '第4週'],
  [$$.lastWeekOfMonth, '最終週']
])

export const resolveRecurrence = Recurrence.resolve
