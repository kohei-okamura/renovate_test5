/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  dwsHomeHelpService: '1011',
  dwsVisitingCareForPwsd: '1012',
  dwsCommAccompany: '1072',
  dwsOthers: '10ZZ',
  ltcsHomeVisitLongTermCare: '2011',
  ltcsCompHomeVisiting: '20A0',
  ltcsCareManagement: '2046',
  ltcsPrevention: '20A4',
  ltcsOthers: '20ZZ'
} as const

/**
 * 事業所：指定区分.
 */
export type OfficeQualification = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const OfficeQualification = createEnumerable($$, [
  [$$.dwsHomeHelpService, '居宅介護（障害福祉サービス）'],
  [$$.dwsVisitingCareForPwsd, '重度訪問介護（障害福祉サービス）'],
  [$$.dwsCommAccompany, '地域生活支援事業・移動支援（障害福祉サービス）'],
  [$$.dwsOthers, 'その他障害福祉サービス'],
  [$$.ltcsHomeVisitLongTermCare, '訪問介護（介護保険サービス）'],
  [$$.ltcsCompHomeVisiting, '総合事業・訪問型サービス（介護保険サービス）'],
  [$$.ltcsCareManagement, '居宅介護支援（介護保険サービス）'],
  [$$.ltcsPrevention, '介護予防支援（介護保険サービス）'],
  [$$.ltcsOthers, 'その他介護保険サービス']
])

export const resolveOfficeQualification = OfficeQualification.resolve
