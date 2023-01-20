/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  basic: 1,
  composed: 2,
  independent: 3
} as const

/**
 * 介護保険サービス：合成識別区分.
 */
export type LtcsCompositionType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsCompositionType = createEnumerable($$, [
  [$$.basic, '基本サービスコード'],
  [$$.composed, '合成サービスコード'],
  [$$.independent, '単独加減算サービスコード']
])

export const resolveLtcsCompositionType = LtcsCompositionType.resolve
