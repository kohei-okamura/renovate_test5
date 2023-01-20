/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  unspecified: 0,
  notApplicable: 1,
  hospitalized: 3,
  died: 4,
  other: 5,
  admittedToWelfareFacility: 6,
  admittedToHealthCareFacility: 7,
  admittedToMedicalLongTermCareSanatoriums: 8,
  admittedToCareAidMedicalCenter: 9
} as const

/**
 * 介護保険サービス：明細書：中止理由.
 */
export type LtcsExpiredReason = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsExpiredReason = createEnumerable($$, [
  [$$.unspecified, '未設定'],
  [$$.notApplicable, '非該当'],
  [$$.hospitalized, '医療機関入院'],
  [$$.died, '死亡'],
  [$$.other, 'その他'],
  [$$.admittedToWelfareFacility, '介護老人福祉施設入所'],
  [$$.admittedToHealthCareFacility, '介護老人保健施設入所'],
  [$$.admittedToMedicalLongTermCareSanatoriums, '介護療養型医療施設入所'],
  [$$.admittedToCareAidMedicalCenter, '介護医療院入所']
])

export const resolveLtcsExpiredReason = LtcsExpiredReason.resolve
