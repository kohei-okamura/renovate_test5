/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  beginner: 1,
  careWorkerForPwsd: 2
} as const

/**
 * 障害福祉サービス：居宅介護：提供者区分.
 */
export type DwsHomeHelpServiceProviderType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsHomeHelpServiceProviderType = createEnumerable($$, [
  [$$.none, '下記に該当しない'],
  [$$.beginner, '基（基礎研修課程修了者等により行われる場合）'],
  [$$.careWorkerForPwsd, '重研（重度訪問介護研修修了者による場合）']
])

export const resolveDwsHomeHelpServiceProviderType = DwsHomeHelpServiceProviderType.resolve
