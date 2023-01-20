/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  physical: 1,
  intellectual: 2,
  mental: 3,
  intractableDiseases: 5
} as const

/**
 * 障害種別.
 */
export type DwsType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsType = createEnumerable($$, [
  [$$.physical, '身体'],
  [$$.intellectual, '知的'],
  [$$.mental, '精神'],
  [$$.intractableDiseases, '難病']
])

export const resolveDwsType = DwsType.resolve
