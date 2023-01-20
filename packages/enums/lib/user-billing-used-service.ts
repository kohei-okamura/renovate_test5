/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  disabilitiesWelfareService: 1,
  longTermCareService: 2,
  ownExpenseService: 3
} as const

/**
 * 利用者請求：利用サービス.
 */
export type UserBillingUsedService = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const UserBillingUsedService = createEnumerable($$, [
  [$$.disabilitiesWelfareService, '障害福祉サービス'],
  [$$.longTermCareService, '介護保険サービス'],
  [$$.ownExpenseService, '自費サービス']
])

export const resolveUserBillingUsedService = UserBillingUsedService.resolve
