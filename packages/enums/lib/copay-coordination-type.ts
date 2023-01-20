/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 1,
  internal: 2,
  external: 3,
  unknown: 9
} as const

/**
 * 上限管理区分.
 */
export type CopayCoordinationType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const CopayCoordinationType = createEnumerable($$, [
  [$$.none, '上限管理なし'],
  [$$.internal, '自社事業所'],
  [$$.external, '他社事業所'],
  [$$.unknown, '不明']
])

export const resolveCopayCoordinationType = CopayCoordinationType.resolve
