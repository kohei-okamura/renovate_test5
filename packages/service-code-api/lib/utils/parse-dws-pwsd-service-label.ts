/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { alt, eof, string, succeed } from 'parsimmon'
import { dotAndOption, minutes, option, patternMatch, seqObject } from './patterns'

/**
 * サービス名称略称から取得できる情報.
 */
type VisitingCareForPwsdLabelInfo = {
  /** サービスコード区分 */
  category: DwsServiceCodeCategory

  /** 時間帯 */
  timeframe: Timeframe

  /** 時間数（分） */
  minutes: number

  /** 2人目の重度訪問介護従業者による場合 */
  isSecondary: boolean

  /** 熟練従業者が同行して支援を行う場合 */
  isCoaching: boolean

  /** 病院等に入院又は入所中に利用した場合 */
  isHospitalized: boolean

  /** 90日以上利用減算 */
  isLongHospitalized: boolean
}

/**
 * サービス提供を表すラベルを解析するパーサーを生成する.
 */
const createServiceLabelParser = () => {
  const category = patternMatch<DwsServiceCodeCategory>(
    [string('重訪Ⅰ'), DwsServiceCodeCategory.visitingCareForPwsd1],
    [string('重訪Ⅱ'), DwsServiceCodeCategory.visitingCareForPwsd2],
    [string('重訪Ⅲ'), DwsServiceCodeCategory.visitingCareForPwsd3]
  )
  const timeframe = patternMatch<Timeframe>(
    [string('日中'), Timeframe.daytime],
    [string('早朝'), Timeframe.morning],
    [string('夜間'), Timeframe.night],
    [string('深夜'), Timeframe.midnight]
  )
  return alt(
    seqObject<VisitingCareForPwsdLabelInfo>(
      { category },
      { isHospitalized: option('入院等') },
      { timeframe },
      { minutes },
      { isSecondary: dotAndOption('2人') },
      { isCoaching: dotAndOption('同行') },
      { isLongHospitalized: dotAndOption('90日減') },
      eof
    ),
    seqObject<VisitingCareForPwsdLabelInfo>(
      { category: string('重訪移動介護加算').map(_ => DwsServiceCodeCategory.outingSupportForPwsd) },
      { minutes },
      { isSecondary: dotAndOption('2人') },
      { isCoaching: dotAndOption('同行') },
      { timeframe: succeed(Timeframe.unknown) },
      { isHospitalized: succeed(false) },
      { isLongHospitalized: succeed(false) },
      eof
    )
  )
}

/**
 * 加算関連のラベルを解析するパーサーを生成する.
 */
const createAdditionLabelParser = (pattern: string, category: DwsServiceCodeCategory) => string(pattern)
  .map<VisitingCareForPwsdLabelInfo>(_ => ({
    category,
    isSecondary: false,
    isCoaching: false,
    isHospitalized: false,
    isLongHospitalized: false,
    timeframe: Timeframe.unknown,
    minutes: 0
  }))

/**
 * サービスコード（重度訪問介護）の名称（略称）を解析するパーサー.
 */
const parser = alt(
  createServiceLabelParser(),
  createAdditionLabelParser('重訪特定事業所加算Ⅰ', DwsServiceCodeCategory.specifiedOfficeAddition1),
  createAdditionLabelParser('重訪特定事業所加算Ⅱ', DwsServiceCodeCategory.specifiedOfficeAddition2),
  createAdditionLabelParser('重訪特定事業所加算Ⅲ', DwsServiceCodeCategory.specifiedOfficeAddition3),
  createAdditionLabelParser('重訪特地加算', DwsServiceCodeCategory.specifiedAreaAddition),
  createAdditionLabelParser('重訪緊急時対応加算（地域生活拠点）', DwsServiceCodeCategory.emergencyAddition2),
  createAdditionLabelParser('重訪緊急時対応加算', DwsServiceCodeCategory.emergencyAddition1),
  createAdditionLabelParser('重訪喀痰吸引等支援体制加算', DwsServiceCodeCategory.suckingSupportSystemAddition),
  createAdditionLabelParser('重訪初回加算', DwsServiceCodeCategory.firstTimeAddition),
  createAdditionLabelParser('重訪上限額管理加算', DwsServiceCodeCategory.copayCoordinationAddition),
  createAdditionLabelParser('重訪行動障害支援連携加算', DwsServiceCodeCategory.behavioralDisorderSupportCooperationAddition),
  createAdditionLabelParser('重訪処遇改善加算Ⅰ', DwsServiceCodeCategory.treatmentImprovementAddition1),
  createAdditionLabelParser('重訪処遇改善加算Ⅱ', DwsServiceCodeCategory.treatmentImprovementAddition2),
  createAdditionLabelParser('重訪処遇改善加算Ⅲ', DwsServiceCodeCategory.treatmentImprovementAddition3),
  createAdditionLabelParser('重訪処遇改善加算Ⅳ', DwsServiceCodeCategory.treatmentImprovementAddition4),
  createAdditionLabelParser('重訪処遇改善加算Ⅴ', DwsServiceCodeCategory.treatmentImprovementAddition5),
  createAdditionLabelParser('重訪処遇改善特別加算', DwsServiceCodeCategory.treatmentImprovementSpecialAddition),
  createAdditionLabelParser('重訪特定処遇改善加算Ⅰ', DwsServiceCodeCategory.specifiedTreatmentImprovementAddition1),
  createAdditionLabelParser('重訪特定処遇改善加算Ⅱ', DwsServiceCodeCategory.specifiedTreatmentImprovementAddition2),
  createAdditionLabelParser('令和3年9月30日までの上乗せ分（重訪）', DwsServiceCodeCategory.covid19PandemicSpecialAddition),
  createAdditionLabelParser('重訪身体拘束廃止未実施減算', DwsServiceCodeCategory.physicalRestraintSubtraction),
  createAdditionLabelParser('重訪移動介護緊急時支援加算', DwsServiceCodeCategory.movingCareSupportAddition),
  createAdditionLabelParser('重訪ベースアップ等支援加算', DwsServiceCodeCategory.baseIncreaseSupportAddition)
)

/**
 * サービスコード（重度訪問介護）の名称（略称）をパースする.
 */
export const parseDwsPwsdServiceLabel = (input: string): VisitingCareForPwsdLabelInfo => parser.tryParse(input)
