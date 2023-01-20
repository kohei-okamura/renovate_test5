/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  durationMinutes: 1,
  none: 99
} as const

/**
 * 介護保険サービス：摘要欄記載条件.
 */
export type LtcsNoteRequirement = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsNoteRequirement = createEnumerable($$, [
  [$$.durationMinutes, '所要時間'],
  [$$.none, '空白（記載不要）']
])

export const resolveLtcsNoteRequirement = LtcsNoteRequirement.resolve
