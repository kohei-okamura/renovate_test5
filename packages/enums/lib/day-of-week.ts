/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  mon: 1,
  tue: 2,
  wed: 3,
  thu: 4,
  fri: 5,
  sat: 6,
  sun: 7
} as const

/**
 * 曜日.
 */
export type DayOfWeek = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DayOfWeek = createEnumerable($$, [
  [$$.mon, '月'],
  [$$.tue, '火'],
  [$$.wed, '水'],
  [$$.thu, '木'],
  [$$.fri, '金'],
  [$$.sat, '土'],
  [$$.sun, '日']
])

export const resolveDayOfWeek = DayOfWeek.resolve
