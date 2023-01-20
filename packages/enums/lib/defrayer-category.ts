/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  pwdSupport: 58,
  atomicBombVictim: 81,
  supportForJapaneseReturneesFromChina: 25,
  livelihoodProtection: 12
} as const

/**
 * 公費制度（法別番号）.
 */
export type DefrayerCategory = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DefrayerCategory = createEnumerable($$, [
  [$$.pwdSupport, '【58】特別対策（全額免除）'],
  [$$.atomicBombVictim, '【81】原爆（福祉）'],
  [$$.supportForJapaneseReturneesFromChina, '【25】中国残留邦人'],
  [$$.livelihoodProtection, '【12】生活保護']
])

export const resolveDefrayerCategory = DefrayerCategory.resolve
