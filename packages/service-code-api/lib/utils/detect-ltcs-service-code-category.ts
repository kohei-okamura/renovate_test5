/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'

/**
 * サービスコード区分を特定する.
 */
export const detectLtcsServiceCodeCategory = (row: string[], name: string): LtcsServiceCodeCategory => {
  switch (+row[35]) {
    case 1:
      return LtcsServiceCodeCategory.physicalCare
    case 6:
      return LtcsServiceCodeCategory.housework
    case 7:
      return LtcsServiceCodeCategory.physicalCareAndHousework
    case 8:
      throw new Error('通院等乗降介助はサポートしていません')
    default:
      switch (name) {
        case '緊急時訪問介護加算':
          return LtcsServiceCodeCategory.emergencyAddition
        case '訪問介護初回加算':
          return LtcsServiceCodeCategory.firstTimeAddition
        case '訪問介護生活機能向上連携加算Ⅰ':
          return LtcsServiceCodeCategory.vitalFunctionsImprovementAddition1
        case '訪問介護生活機能向上連携加算Ⅱ':
          return LtcsServiceCodeCategory.vitalFunctionsImprovementAddition2
        case '訪問介護同一建物減算1':
          return LtcsServiceCodeCategory.bulkServiceSubtraction1
        case '訪問介護同一建物減算2':
          return LtcsServiceCodeCategory.bulkServiceSubtraction2
        case '訪問介護処遇改善加算Ⅰ':
          return LtcsServiceCodeCategory.treatmentImprovementAddition1
        case '訪問介護処遇改善加算Ⅱ':
          return LtcsServiceCodeCategory.treatmentImprovementAddition2
        case '訪問介護処遇改善加算Ⅲ':
          return LtcsServiceCodeCategory.treatmentImprovementAddition3
        case '訪問介護処遇改善加算Ⅳ':
          return LtcsServiceCodeCategory.treatmentImprovementAddition4
        case '訪問介護処遇改善加算Ⅴ':
          return LtcsServiceCodeCategory.treatmentImprovementAddition5
        case '訪問介護特定処遇改善加算Ⅰ':
          return LtcsServiceCodeCategory.specifiedTreatmentImprovementAddition1
        case '訪問介護特定処遇改善加算Ⅱ':
          return LtcsServiceCodeCategory.specifiedTreatmentImprovementAddition2
        case '訪問介護共生型サービス居宅介護1':
          return LtcsServiceCodeCategory.symbioticServiceSubtraction1
        case '訪問介護共生型サービス居宅介護2':
          return LtcsServiceCodeCategory.symbioticServiceSubtraction1
        case '訪問介護共生型サービス重度訪問介護':
          return LtcsServiceCodeCategory.symbioticServiceSubtraction3
        case '特別地域訪問介護加算':
          return LtcsServiceCodeCategory.specifiedAreaAddition
        case '訪問介護小規模事業所加算':
          return LtcsServiceCodeCategory.smallOfficeAddition
        case '訪問介護中山間地域等提供加算':
          return LtcsServiceCodeCategory.mountainousAreaAddition
        case '訪問介護特定事業所加算Ⅰ':
          return LtcsServiceCodeCategory.specifiedOfficeAddition1
        case '訪問介護特定事業所加算Ⅱ':
          return LtcsServiceCodeCategory.specifiedOfficeAddition2
        case '訪問介護特定事業所加算Ⅲ':
          return LtcsServiceCodeCategory.specifiedOfficeAddition3
        case '訪問介護特定事業所加算Ⅳ':
          return LtcsServiceCodeCategory.specifiedOfficeAddition4
        case '訪問介護特定事業所加算Ⅴ':
          return LtcsServiceCodeCategory.specifiedOfficeAddition5
        case '訪問介護認知症専門ケア加算Ⅰ':
          return LtcsServiceCodeCategory.dementiaCareSpecialistAddition1
        case '訪問介護認知症専門ケア加算Ⅱ':
          return LtcsServiceCodeCategory.dementiaCareSpecialistAddition2
        case '訪問介護令和3年9月30日までの上乗せ分':
          return LtcsServiceCodeCategory.covid19PandemicSpecialAddition
        case '訪問介護ベースアップ等支援加算':
          return LtcsServiceCodeCategory.baseIncreaseSupportAddition
        default:
          throw new Error(`未対応のサービスコード: [${row[0]}${row[1]}] ${row[5]}`)
      }
  }
}
