/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  theirself: 10,
  family: 20,
  lawyer: 30,
  others: 99
} as const

/**
 * 連絡先電話番号：続柄・関係.
 */
export type ContactRelationship = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const ContactRelationship = createEnumerable($$, [
  [$$.theirself, '本人'],
  [$$.family, '家族'],
  [$$.lawyer, '弁護士'],
  [$$.others, 'その他']
])

export const resolveContactRelationship = ContactRelationship.resolve
