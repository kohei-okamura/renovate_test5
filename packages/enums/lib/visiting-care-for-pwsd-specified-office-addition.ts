/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  addition1: 1,
  addition2: 2,
  addition3: 3
} as const

/**
 * 障害福祉サービス：重度訪問介護：特定事業所加算区分.
 */
export type VisitingCareForPwsdSpecifiedOfficeAddition = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const VisitingCareForPwsdSpecifiedOfficeAddition = createEnumerable($$, [
  [$$.none, 'なし'],
  [$$.addition1, '特定事業所加算Ⅰ'],
  [$$.addition2, '特定事業所加算Ⅱ'],
  [$$.addition3, '特定事業所加算Ⅲ']
])

export const resolveVisitingCareForPwsdSpecifiedOfficeAddition = VisitingCareForPwsdSpecifiedOfficeAddition.resolve
