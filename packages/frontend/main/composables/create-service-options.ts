/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { resolveServiceOption, ServiceOption } from '@zinger/enums/lib/service-option'
import { Task } from '@zinger/enums/lib/task'

type Option = {
  code: ServiceOption
  name: string
  hint: string
}

const hints = {
  [ServiceOption.notificationEnabled]: '出勤前通知機能を利用する場合にチェックしてください。',
  [ServiceOption.oneOff]: '今回限りの単発的なサービスの場合にチェックしてください。',
  [ServiceOption.firstTime]: '初回加算の対象となるサービスである場合にチェックしてください。',
  [ServiceOption.emergency]: '利用者の要請に基づく緊急時対応である場合にチェックしてください。',
  [ServiceOption.sucking]: '登録特定行為事業者の認定特定行為業務従事者が、医療機関との連携により喀痰吸引等を行った場合にチェックしてください。',
  [ServiceOption.welfareSpecialistCooperation]: '福祉専門職員等連携を行う場合にチェックしてください。',
  [ServiceOption.plannedByNovice]: '初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合にチェックしてください。',
  [ServiceOption.providedByBeginner]: '基礎研修課程修了者等によるサービス提供となる場合にチェックしてください。',
  [ServiceOption.providedByCareWorkerForPwsd]: '重度訪問介護研修修了者によるサービス提供となる場合にチェックしてください。',
  [ServiceOption.over20]: '同一建物減算の対象となる場合にチェックしてください。',
  [ServiceOption.over50]: '同一建物減算（大規模）の対象となる場合にチェックしてください。',
  [ServiceOption.behavioralDisorderSupportCooperation]: '行動障害支援連携を行う場合にチェックしてください。',
  [ServiceOption.hospitalized]: '入院中の利用者に提供するサービスの場合にチェックしてください。',
  [ServiceOption.longHospitalized]: '90日以上の長期入院中の利用者に提供するサービスの場合にチェックしてください。',
  [ServiceOption.coaching]: '熟練同行の対象となるサービスである場合にチェックしてください。',
  [ServiceOption.vitalFunctionsImprovement1]: '生活機能向上連携Ⅰを行う場合にチェックしてください。',
  [ServiceOption.vitalFunctionsImprovement2]: '生活機能向上連携Ⅱを行う場合にチェックしてください。'
}

const getTaskServiceOptionKeys = (task?: Task) => {
  const baseOptions = [
    ServiceOption.notificationEnabled,
    ServiceOption.oneOff
  ]
  switch (task) {
    case Task.dwsPhysicalCare:
    case Task.dwsHousework:
    case Task.dwsAccompanyWithPhysicalCare:
    case Task.dwsAccompany:
      return [
        ...baseOptions,
        ServiceOption.firstTime,
        ServiceOption.emergency,
        ServiceOption.sucking,
        ServiceOption.welfareSpecialistCooperation,
        ServiceOption.plannedByNovice,
        ServiceOption.providedByBeginner,
        ServiceOption.providedByCareWorkerForPwsd,
        ServiceOption.over20,
        ServiceOption.over50
      ]
    case Task.dwsVisitingCareForPwsd:
      return [
        ...baseOptions,
        ServiceOption.firstTime,
        ServiceOption.emergency,
        ServiceOption.sucking,
        ServiceOption.behavioralDisorderSupportCooperation,
        ServiceOption.hospitalized,
        ServiceOption.longHospitalized,
        ServiceOption.coaching
      ]
    case Task.ltcsPhysicalCare:
    case Task.ltcsHousework:
    case Task.ltcsPhysicalCareAndHousework:
      return [
        ...baseOptions,
        ServiceOption.firstTime,
        ServiceOption.emergency,
        ServiceOption.over20,
        ServiceOption.over50,
        ServiceOption.vitalFunctionsImprovement1,
        ServiceOption.vitalFunctionsImprovement2
      ]
    case Task.commAccompanyWithPhysicalCare:
    case Task.commAccompany:
    case Task.comprehensive:
    case Task.ownExpense:
    case Task.fieldwork:
    case Task.assessment:
    case Task.visit:
    case Task.officeWork:
    case Task.sales:
    case Task.meeting:
    case Task.other:
      return baseOptions
    default:
      return []
  }
}

type Feature = 'project' | 'provisionReport'

const getDwsServiceOptionKeys = (feature: Feature, serviceCategory?: DwsProjectServiceCategory) => {
  const baseOptions = [
    ServiceOption.sucking,
    ServiceOption.welfareSpecialistCooperation,
    ServiceOption.plannedByNovice,
    ServiceOption.providedByBeginner,
    ServiceOption.providedByCareWorkerForPwsd,
    ServiceOption.over20,
    ServiceOption.over50
  ]
  const baseVisitingCareOptions = [
    ServiceOption.sucking,
    ServiceOption.behavioralDisorderSupportCooperation,
    ServiceOption.hospitalized,
    ServiceOption.longHospitalized,
    ServiceOption.coaching
  ]
  if (feature === 'project') {
    switch (serviceCategory) {
      case DwsProjectServiceCategory.physicalCare:
      case DwsProjectServiceCategory.housework:
      case DwsProjectServiceCategory.accompanyWithPhysicalCare:
      case DwsProjectServiceCategory.accompany:
        return baseOptions
      case DwsProjectServiceCategory.visitingCareForPwsd:
        return baseVisitingCareOptions
      case DwsProjectServiceCategory.ownExpense:
      default:
        return []
    }
  } else {
    switch (serviceCategory) {
      case DwsProjectServiceCategory.physicalCare:
      case DwsProjectServiceCategory.housework:
      case DwsProjectServiceCategory.accompanyWithPhysicalCare:
      case DwsProjectServiceCategory.accompany:
        return [
          ServiceOption.firstTime,
          ServiceOption.emergency,
          ...baseOptions
        ]
      case DwsProjectServiceCategory.visitingCareForPwsd:
        return [
          ServiceOption.firstTime,
          ServiceOption.emergency,
          ...baseVisitingCareOptions
        ]
      case DwsProjectServiceCategory.ownExpense:
      default:
        return []
    }
  }
}

const getLtcsServiceOptionKeys = (feature: Feature, serviceCategory?: LtcsProjectServiceCategory) => {
  const baseOptions = [
    ServiceOption.over20,
    ServiceOption.over50,
    ServiceOption.vitalFunctionsImprovement1,
    ServiceOption.vitalFunctionsImprovement2
  ]
  if (feature === 'project') {
    switch (serviceCategory) {
      case LtcsProjectServiceCategory.physicalCare:
      case LtcsProjectServiceCategory.housework:
      case LtcsProjectServiceCategory.physicalCareAndHousework:
        return baseOptions
      case LtcsProjectServiceCategory.ownExpense:
      default:
        return []
    }
  } else {
    switch (serviceCategory) {
      case LtcsProjectServiceCategory.physicalCare:
      case LtcsProjectServiceCategory.housework:
      case LtcsProjectServiceCategory.physicalCareAndHousework:
        return [
          ServiceOption.firstTime,
          ServiceOption.emergency,
          ...baseOptions
        ]
      case LtcsProjectServiceCategory.ownExpense:
      default:
        return []
    }
  }
}

const serviceOptionsMap: Record<ServiceOption, Option> = Object.fromEntries(ServiceOption.values.map(v => {
  return [[v], Object.freeze({ code: v, name: resolveServiceOption(v), hint: hints[v] })]
}))

const createServiceOptions = (keys: ServiceOption[]): Option[] => {
  return keys.map(key => serviceOptionsMap[key])
}

/**
 * 勤務区分に紐づくサービスオプション選択肢を作成する
 * 勤務区分未指定、もしくは紐づく選択肢がない場合は空配列を返す
 *
 * @param task 勤務区分
 */
export const createTaskServiceOptions = (task?: Task): Option[] => {
  const keys = getTaskServiceOptionKeys(task)
  return createServiceOptions(keys)
}

/**
 * 障害福祉サービス：計画：サービス区分に紐づくサービスオプション選択肢を作成する
 * サービス区分が未指定、もしくは紐づく選択肢がない場合は空配列を返す
 *
 * @param feature 対象の機能 'project': 計画、'provisionReport': 予実
 * @param serviceCategory 障害福祉サービス：計画：サービス区分
 */
export const createDwsServiceOptions = (feature: Feature, serviceCategory?: DwsProjectServiceCategory): Option[] => {
  const keys = getDwsServiceOptionKeys(feature, serviceCategory)
  return createServiceOptions(keys)
}

/**
 * 介護保険サービス：計画：サービス区分に紐づくサービスオプション選択肢を作成する
 * サービス区分が未指定、もしくは紐づく選択肢がない場合は空配列を返す
 *
 * @param feature 対象の機能 'project': 計画、'provisionReport': 予実
 * @param serviceCategory 介護保険サービス：計画：サービス区分
 */
export const createLtcsServiceOptions = (feature: Feature, serviceCategory?: LtcsProjectServiceCategory): Option[] => {
  const keys = getLtcsServiceOptionKeys(feature, serviceCategory)
  return createServiceOptions(keys)
}
