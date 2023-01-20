/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  addition1: 1
} as const

/**
 * 介護保険サービス：ベースアップ等支援加算.
 */
export type LtcsBaseIncreaseSupportAddition = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsBaseIncreaseSupportAddition = createEnumerable($$, [
  [$$.none, 'なし'],
  [$$.addition1, '介護職員等ベースアップ等支援加算']
])

export const resolveLtcsBaseIncreaseSupportAddition = LtcsBaseIncreaseSupportAddition.resolve
