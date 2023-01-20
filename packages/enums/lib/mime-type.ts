/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  csv: 'text/csv',
  pdf: 'application/pdf'
} as const

/**
 * MimeType.
 */
export type MimeType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const MimeType = createEnumerable($$, [
  [$$.csv, 'CSV'],
  [$$.pdf, 'PDF']
])

export const resolveMimeType = MimeType.resolve
