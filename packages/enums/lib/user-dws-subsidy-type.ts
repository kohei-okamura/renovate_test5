/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  benefitRate: 1,
  copayRate: 4,
  benefitAmount: 2,
  copayAmount: 3
} as const

/**
 * 利用者：自治体助成情報：給付方式.
 */
export type UserDwsSubsidyType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const UserDwsSubsidyType = createEnumerable($$, [
  [$$.benefitRate, '定率給付'],
  [$$.copayRate, '定率負担'],
  [$$.benefitAmount, '定額給付'],
  [$$.copayAmount, '定額負担']
])

export const resolveUserDwsSubsidyType = UserDwsSubsidyType.resolve
