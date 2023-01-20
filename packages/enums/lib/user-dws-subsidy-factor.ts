/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  fee: 1,
  copay: 2
} as const

/**
 * 利用者：自治体助成情報：基準値種別.
 */
export type UserDwsSubsidyFactor = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const UserDwsSubsidyFactor = createEnumerable($$, [
  [$$.none, '未設定'],
  [$$.fee, '総費用額'],
  [$$.copay, '決定利用者負担額']
])

export const resolveUserDwsSubsidyFactor = UserDwsSubsidyFactor.resolve
