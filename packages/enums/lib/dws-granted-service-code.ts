/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: '000000',
  physicalCare: '111000',
  housework: '112000',
  accompanyWithPhysicalCare: '113000',
  accompany: '114000',
  visitingCareForPwsd1: '121000',
  visitingCareForPwsd2: '122000',
  visitingCareForPwsd3: '123000',
  outingSupportForPwsd: '120901',
  comprehensiveSupport: '141000'
} as const

/**
 * 障害福祉サービス：決定サービスコード.
 */
export type DwsGrantedServiceCode = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsGrantedServiceCode = createEnumerable($$, [
  [$$.none, 'なし'],
  [$$.physicalCare, '居宅介護身体介護'],
  [$$.housework, '居宅介護家事援助'],
  [$$.accompanyWithPhysicalCare, '居宅介護通院介助（身体介護を伴う）'],
  [$$.accompany, '居宅介護通院介助（身体介護を伴わない）'],
  [$$.visitingCareForPwsd1, '重度訪問介護（重度障害者等包括支援対象者）'],
  [$$.visitingCareForPwsd2, '重度訪問介護（障害支援区分6該当者）'],
  [$$.visitingCareForPwsd3, '重度訪問介護（その他）'],
  [$$.outingSupportForPwsd, '重度訪問介護（移動加算）'],
  [$$.comprehensiveSupport, '重度包括基本']
])

export const resolveDwsGrantedServiceCode = DwsGrantedServiceCode.resolve
