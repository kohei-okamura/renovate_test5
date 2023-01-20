/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  inPreparation: 1,
  inOperation: 2,
  suspended: 8,
  closed: 9
} as const

/**
 * 事業所：状態.
 */
export type OfficeStatus = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const OfficeStatus = createEnumerable($$, [
  [$$.inPreparation, '準備中'],
  [$$.inOperation, '運営中'],
  [$$.suspended, '休止'],
  [$$.closed, '廃止']
])

export const resolveOfficeStatus = OfficeStatus.resolve
