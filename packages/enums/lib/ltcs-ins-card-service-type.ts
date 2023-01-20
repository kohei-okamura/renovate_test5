/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  serviceType1: 1,
  serviceType2: 2,
  serviceType3: 3
} as const

/**
 * 介護保険被保険者証：サービスの種類.
 */
export type LtcsInsCardServiceType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsInsCardServiceType = createEnumerable($$, [
  [$$.serviceType1, 'サービス種別1'],
  [$$.serviceType2, 'サービス種別2'],
  [$$.serviceType3, 'サービス種別3']
])

export const resolveLtcsInsCardServiceType = LtcsInsCardServiceType.resolve
