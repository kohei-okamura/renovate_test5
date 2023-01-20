/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  physicalCare: 11,
  housework: 12,
  physicalCareAndHousework: 13,
  ownExpense: 91
} as const

/**
 * 介護保険サービス：計画：サービス区分.
 */
export type LtcsProjectServiceCategory = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsProjectServiceCategory = createEnumerable($$, [
  [$$.physicalCare, '身体介護'],
  [$$.housework, '生活援助'],
  [$$.physicalCareAndHousework, '身体・生活'],
  [$$.ownExpense, '自費']
])

export const resolveLtcsProjectServiceCategory = LtcsProjectServiceCategory.resolve
