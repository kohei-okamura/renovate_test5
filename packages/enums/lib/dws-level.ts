/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  notApplicable: 99,
  level1: 21,
  level2: 22,
  level3: 23,
  level4: 24,
  level5: 25,
  level6: 26
} as const

/**
 * 障害支援区分.
 */
export type DwsLevel = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsLevel = createEnumerable($$, [
  [$$.notApplicable, '非該当'],
  [$$.level1, '区分1'],
  [$$.level2, '区分2'],
  [$$.level3, '区分3'],
  [$$.level4, '区分4'],
  [$$.level5, '区分5'],
  [$$.level6, '区分6']
])

export const resolveDwsLevel = DwsLevel.resolve
