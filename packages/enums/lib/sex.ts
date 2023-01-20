/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  notKnown: 0,
  male: 1,
  female: 2,
  notApplicable: 9
} as const

/**
 * 性別（ISO 5218）.
 */
export type Sex = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Sex = createEnumerable($$, [
  [$$.notKnown, '不明'],
  [$$.male, '男性'],
  [$$.female, '女性'],
  [$$.notApplicable, '適用不能']
])

export const resolveSex = Sex.resolve
