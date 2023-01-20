/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  physicalCare: 111000,
  physicalCareAndHousework: 112000,
  housework: 113000,
  emergencyAddition: 990101,
  firstTimeAddition: 990201,
  vitalFunctionsImprovementAddition1: 990301,
  vitalFunctionsImprovementAddition2: 990302,
  bulkServiceSubtraction1: 990401,
  bulkServiceSubtraction2: 990402,
  treatmentImprovementAddition1: 990501,
  treatmentImprovementAddition2: 990502,
  treatmentImprovementAddition3: 990503,
  treatmentImprovementAddition4: 990504,
  treatmentImprovementAddition5: 990505,
  specifiedTreatmentImprovementAddition1: 990601,
  specifiedTreatmentImprovementAddition2: 990602,
  symbioticServiceSubtraction1: 990701,
  symbioticServiceSubtraction2: 990702,
  symbioticServiceSubtraction3: 990711,
  specifiedAreaAddition: 990801,
  smallOfficeAddition: 990901,
  mountainousAreaAddition: 991001,
  specifiedOfficeAddition1: 991101,
  specifiedOfficeAddition2: 991102,
  specifiedOfficeAddition3: 991103,
  specifiedOfficeAddition4: 991104,
  specifiedOfficeAddition5: 991105,
  dementiaCareSpecialistAddition1: 991201,
  dementiaCareSpecialistAddition2: 991202,
  covid19PandemicSpecialAddition: 991301,
  baseIncreaseSupportAddition: 991401
} as const

/**
 * 介護保険サービス：請求：サービスコード区分.
 */
export type LtcsServiceCodeCategory = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsServiceCodeCategory = createEnumerable($$, [
  [$$.physicalCare, '身体'],
  [$$.physicalCareAndHousework, '身体＋生活'],
  [$$.housework, '生活'],
  [$$.emergencyAddition, '緊急時訪問介護加算'],
  [$$.firstTimeAddition, '初回加算'],
  [$$.vitalFunctionsImprovementAddition1, '生活機能向上連携加算Ⅰ'],
  [$$.vitalFunctionsImprovementAddition2, '生活機能向上連携加算Ⅱ'],
  [$$.bulkServiceSubtraction1, '同一建物減算Ⅰ'],
  [$$.bulkServiceSubtraction2, '同一建物減算Ⅱ'],
  [$$.treatmentImprovementAddition1, '介護職員処遇改善加算Ⅰ'],
  [$$.treatmentImprovementAddition2, '介護職員処遇改善加算Ⅱ'],
  [$$.treatmentImprovementAddition3, '介護職員処遇改善加算Ⅲ'],
  [$$.treatmentImprovementAddition4, '介護職員処遇改善加算Ⅳ'],
  [$$.treatmentImprovementAddition5, '介護職員処遇改善加算Ⅴ'],
  [$$.specifiedTreatmentImprovementAddition1, '介護職員等特定処遇改善加算Ⅰ'],
  [$$.specifiedTreatmentImprovementAddition2, '介護職員等特定処遇改善加算Ⅱ'],
  [$$.symbioticServiceSubtraction1, '共生型サービス減算（居宅介護1）'],
  [$$.symbioticServiceSubtraction2, '共生型サービス減算（居宅介護2）'],
  [$$.symbioticServiceSubtraction3, '共生型サービス減算（重度訪問介護）'],
  [$$.specifiedAreaAddition, '特別地域訪問介護加算'],
  [$$.smallOfficeAddition, '小規模事業所加算（中山間地域等における小規模事業所加算）'],
  [$$.mountainousAreaAddition, '中山間地域等提供加算（中山間地域等に居住する者へのサービス提供加算）'],
  [$$.specifiedOfficeAddition1, '特定事業所加算Ⅰ'],
  [$$.specifiedOfficeAddition2, '特定事業所加算Ⅱ'],
  [$$.specifiedOfficeAddition3, '特定事業所加算Ⅲ'],
  [$$.specifiedOfficeAddition4, '特定事業所加算Ⅳ'],
  [$$.specifiedOfficeAddition5, '特定事業所加算Ⅴ'],
  [$$.dementiaCareSpecialistAddition1, '認知症専門ケア加算Ⅰ'],
  [$$.dementiaCareSpecialistAddition2, '認知症専門ケア加算Ⅱ'],
  [$$.covid19PandemicSpecialAddition, '令和3年9月30日までの上乗せ分'],
  [$$.baseIncreaseSupportAddition, '訪問介護ベースアップ等支援加算']
])

export const resolveLtcsServiceCodeCategory = LtcsServiceCodeCategory.resolve
