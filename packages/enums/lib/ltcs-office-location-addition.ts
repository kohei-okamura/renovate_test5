/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  specifiedArea: 1,
  mountainousArea: 2
} as const

/**
 * 介護保険サービス：地域加算区分.
 */
export type LtcsOfficeLocationAddition = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsOfficeLocationAddition = createEnumerable($$, [
  [$$.none, 'なし'],
  [$$.specifiedArea, '特別地域訪問介護加算'],
  [$$.mountainousArea, '中山間地域等における小規模事業所加算']
])

export const resolveLtcsOfficeLocationAddition = LtcsOfficeLocationAddition.resolve
