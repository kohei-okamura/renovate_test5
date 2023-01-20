/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  applied: 1,
  approved: 2
} as const

/**
 * 障害福祉サービス受給者証：認定区分.
 */
export type DwsCertificationStatus = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsCertificationStatus = createEnumerable($$, [
  [$$.applied, '申請中'],
  [$$.approved, '認定済']
])

export const resolveDwsCertificationStatus = DwsCertificationStatus.resolve
