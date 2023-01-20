/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  specifiedArea: 1
} as const

/**
 * 障害福祉サービス：利用者別地域加算区分.
 */
export type DwsUserLocationAddition = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsUserLocationAddition = createEnumerable($$, [
  [$$.none, 'なし'],
  [$$.specifiedArea, '特別地域加算']
])

export const resolveDwsUserLocationAddition = DwsUserLocationAddition.resolve
