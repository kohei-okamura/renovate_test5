/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  firstTime: 1,
  change: 2,
  other: 0
} as const

/**
 * 全銀レコード：データレコード：新規コード.
 */
export type ZenginDataRecordCode = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const ZenginDataRecordCode = createEnumerable($$, [
  [$$.firstTime, '初回'],
  [$$.change, '変更'],
  [$$.other, 'その他']
])

export const resolveZenginDataRecordCode = ZenginDataRecordCode.resolve
