/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  physicalCare: 11,
  housework: 12,
  accompanyWithPhysicalCare: 13,
  accompany: 14,
  visitingCareForPwsd1: 21,
  visitingCareForPwsd2: 22,
  visitingCareForPwsd3: 23,
  outingSupportForPwsd: 29
} as const

/**
 * 障害福祉サービス受給者証：サービス内容.
 */
export type DwsCertificationAgreementType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsCertificationAgreementType = createEnumerable($$, [
  [$$.physicalCare, '身体介護'],
  [$$.housework, '家事援助'],
  [$$.accompanyWithPhysicalCare, '通院介助（身体介護を伴う）'],
  [$$.accompany, '通院介助（身体介護を伴わない）'],
  [$$.visitingCareForPwsd1, '重度訪問介護（重度障害者等包括支援対象者）'],
  [$$.visitingCareForPwsd2, '重度訪問介護（障害支援区分6該当者）'],
  [$$.visitingCareForPwsd3, '重度訪問介護（その他）'],
  [$$.outingSupportForPwsd, '重度訪問介護（移動加算）']
])

export const resolveDwsCertificationAgreementType = DwsCertificationAgreementType.resolve
