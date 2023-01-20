/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  floor: 1,
  ceil: 2,
  round: 3
} as const

/**
 * 端数処理区分.
 */
export type Rounding = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Rounding = createEnumerable($$, [
  [$$.none, '未設定'],
  [$$.floor, '切り捨て'],
  [$$.ceil, '切り上げ'],
  [$$.round, '四捨五入']
])

export const resolveRounding = Rounding.resolve
