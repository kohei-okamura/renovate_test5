/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  mountainousArea: 1
} as const

/**
 * 介護保険サービス：利用者別地域加算区分.
 */
export type LtcsUserLocationAddition = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsUserLocationAddition = createEnumerable($$, [
  [$$.none, 'なし'],
  [$$.mountainousArea, '中山間地域等に居住する者へのサービス提供加算']
])

export const resolveLtcsUserLocationAddition = LtcsUserLocationAddition.resolve
