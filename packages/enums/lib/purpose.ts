/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  unknown: 0,
  internal: 1,
  external: 2
} as const

/**
 * 事業所区分.
 */
export type Purpose = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Purpose = createEnumerable($$, [
  [$$.unknown, '不明'],
  [$$.internal, '自社'],
  [$$.external, '他社']
])

export const resolvePurpose = Purpose.resolve
