/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  physicalCare: 111000,
  housework: 112000,
  accompanyWithPhysicalCare: 113000,
  accompany: 114000,
  accessibleTaxi: 115000,
  visitingCareForPwsd1: 121000,
  visitingCareForPwsd2: 122000,
  visitingCareForPwsd3: 123000,
  outingSupportForPwsd: 120901,
  specifiedOfficeAddition1: 990101,
  specifiedOfficeAddition2: 990102,
  specifiedOfficeAddition3: 990103,
  specifiedOfficeAddition4: 990104,
  specifiedAreaAddition: 990201,
  emergencyAddition1: 990301,
  emergencyAddition2: 990302,
  suckingSupportSystemAddition: 990401,
  firstTimeAddition: 990501,
  copayCoordinationAddition: 990601,
  welfareSpecialistCooperationAddition: 990701,
  behavioralDisorderSupportCooperationAddition: 990702,
  treatmentImprovementAddition1: 990801,
  treatmentImprovementAddition2: 990802,
  treatmentImprovementAddition3: 990803,
  treatmentImprovementAddition4: 990804,
  treatmentImprovementAddition5: 990805,
  treatmentImprovementSpecialAddition: 990901,
  specifiedTreatmentImprovementAddition1: 991001,
  specifiedTreatmentImprovementAddition2: 991002,
  covid19PandemicSpecialAddition: 991101,
  bulkServiceSubtraction1: 991201,
  bulkServiceSubtraction2: 991202,
  physicalRestraintSubtraction: 991301,
  movingCareSupportAddition: 991401,
  baseIncreaseSupportAddition: 991501
} as const

/**
 * 障害福祉サービス：請求：サービスコード区分.
 */
export type DwsServiceCodeCategory = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsServiceCodeCategory = createEnumerable($$, [
  [$$.physicalCare, '居宅：身体'],
  [$$.housework, '居宅：家事'],
  [$$.accompanyWithPhysicalCare, '居宅：通院・身体あり'],
  [$$.accompany, '居宅：通院・身体なし'],
  [$$.accessibleTaxi, '居宅：乗降介助'],
  [$$.visitingCareForPwsd1, '重訪Ⅰ（重度障害者等の場合）'],
  [$$.visitingCareForPwsd2, '重訪Ⅱ（障害支援区分6に該当する者の場合）'],
  [$$.visitingCareForPwsd3, '重訪Ⅲ'],
  [$$.outingSupportForPwsd, '重訪（移動加算）'],
  [$$.specifiedOfficeAddition1, '特定事業所加算Ⅰ'],
  [$$.specifiedOfficeAddition2, '特定事業所加算Ⅱ'],
  [$$.specifiedOfficeAddition3, '特定事業所加算Ⅲ'],
  [$$.specifiedOfficeAddition4, '特定事業所加算Ⅳ'],
  [$$.specifiedAreaAddition, '特別地域加算'],
  [$$.emergencyAddition1, '緊急時対応加算'],
  [$$.emergencyAddition2, '緊急時対応加算（地域生活拠点）'],
  [$$.suckingSupportSystemAddition, '喀痰吸引等支援体制加算'],
  [$$.firstTimeAddition, '初回加算'],
  [$$.copayCoordinationAddition, '利用者負担上限額管理加算'],
  [$$.welfareSpecialistCooperationAddition, '福祉専門職員等連携加算'],
  [$$.behavioralDisorderSupportCooperationAddition, '行動障害支援連携加算'],
  [$$.treatmentImprovementAddition1, '福祉・介護職員処遇改善加算Ⅰ'],
  [$$.treatmentImprovementAddition2, '福祉・介護職員処遇改善加算Ⅱ'],
  [$$.treatmentImprovementAddition3, '福祉・介護職員処遇改善加算Ⅲ'],
  [$$.treatmentImprovementAddition4, '福祉・介護職員処遇改善加算Ⅳ'],
  [$$.treatmentImprovementAddition5, '福祉・介護職員処遇改善加算Ⅴ'],
  [$$.treatmentImprovementSpecialAddition, '福祉・介護職員処遇改善特別加算'],
  [$$.specifiedTreatmentImprovementAddition1, '福祉・介護職員等特定処遇改善加算Ⅰ'],
  [$$.specifiedTreatmentImprovementAddition2, '福祉・介護職員等特定処遇改善加算Ⅱ'],
  [$$.covid19PandemicSpecialAddition, '令和3年9月30日までの上乗せ分'],
  [$$.bulkServiceSubtraction1, '同一建物減算1'],
  [$$.bulkServiceSubtraction2, '同一建物減算2'],
  [$$.physicalRestraintSubtraction, '身体拘束廃止未実施減算'],
  [$$.movingCareSupportAddition, '移動介護緊急時支援加算'],
  [$$.baseIncreaseSupportAddition, '福祉・介護職員等ベースアップ等支援加算']
])

export const resolveDwsServiceCodeCategory = DwsServiceCodeCategory.resolve
