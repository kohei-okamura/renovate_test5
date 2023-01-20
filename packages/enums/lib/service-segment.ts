/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  disabilitiesWelfare: 1,
  longTermCare: 2,
  comprehensive: 3,
  communityLifeSupport: 4,
  ownExpense: 7,
  other: 9
} as const

/**
 * 事業領域.
 */
export type ServiceSegment = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const ServiceSegment = createEnumerable($$, [
  [$$.disabilitiesWelfare, '障害福祉サービス'],
  [$$.longTermCare, '介護保険サービス'],
  [$$.comprehensive, '総合事業'],
  [$$.communityLifeSupport, '地域生活支援事業'],
  [$$.ownExpense, '自費サービス'],
  [$$.other, 'その他']
])

export const resolveServiceSegment = ServiceSegment.resolve
