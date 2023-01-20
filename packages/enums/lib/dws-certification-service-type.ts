/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  physicalCare: 1,
  housework: 2,
  accompanyWithPhysicalCare: 3,
  accompany: 4,
  visitingCareForPwsd1: 7,
  visitingCareForPwsd2: 8,
  visitingCareForPwsd3: 9
} as const

/**
 * 障害福祉サービス受給者証：サービス種別.
 */
export type DwsCertificationServiceType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsCertificationServiceType = createEnumerable($$, [
  [$$.physicalCare, '居宅介護：居宅における身体介護中心'],
  [$$.housework, '居宅介護：家事援助中心'],
  [$$.accompanyWithPhysicalCare, '居宅介護：通院等介助（身体介護を伴う場合）中心'],
  [$$.accompany, '居宅介護：通院等介助（身体介護を伴わない場合）中心'],
  [$$.visitingCareForPwsd1, '重度訪問介護（重度障害者等包括支援対象者）'],
  [$$.visitingCareForPwsd2, '重度訪問介護（障害支援区分6該当者）'],
  [$$.visitingCareForPwsd3, '重度訪問介護（その他）']
])

export const resolveDwsCertificationServiceType = DwsCertificationServiceType.resolve
