/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  target: 6,
  supportLevel1: 12,
  supportLevel2: 13,
  careLevel1: 21,
  careLevel2: 22,
  careLevel3: 23,
  careLevel4: 24,
  careLevel5: 25
} as const

/**
 * 要介護度（要介護状態区分等）.
 */
export type LtcsLevel = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsLevel = createEnumerable($$, [
  [$$.target, '事業対象者'],
  [$$.supportLevel1, '要支援1'],
  [$$.supportLevel2, '要支援2'],
  [$$.careLevel1, '要介護1'],
  [$$.careLevel2, '要介護2'],
  [$$.careLevel3, '要介護3'],
  [$$.careLevel4, '要介護4'],
  [$$.careLevel5, '要介護5']
])

export const resolveLtcsLevel = LtcsLevel.resolve
