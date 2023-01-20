/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsHomeHelpServiceBuildingType } from '@zinger/enums/lib/dws-home-help-service-building-type'
import { DwsHomeHelpServiceProviderType } from '@zinger/enums/lib/dws-home-help-service-provider-type'
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { omit } from '@zinger/helpers'
import { alt, eof, Parser, string } from 'parsimmon'
import { dot, dotAndOption, empty, minutes, option, patternMatch, seqObject } from './patterns'

/**
 * 時間情報.
 */
export type Time = {
  /** 時間帯 */
  timeframe: Timeframe

  /** 増分フラグ */
  isExtra: boolean

  /** 時間数（分） */
  minutes: number
}

/**
 * サービス名称略称から取得できる情報（変換後）.
 */
export type HomeHelpServiceLabelInfo = {
  /** サービスコード区分 */
  category: DwsServiceCodeCategory

  /** 時間情報 */
  times: Time[]

  /** 初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合 */
  isPlannedByNovice: boolean

  /** 2人目の居宅介護従業者による場合 */
  isSecondary: boolean

  /** 建物区分 */
  buildingType: DwsHomeHelpServiceBuildingType

  /** 提供者区分 */
  providerType: DwsHomeHelpServiceProviderType
}

/**
 * サービス名称略称から取得できる情報（変換前）.
 */
type RawLabelInfo = Omit<HomeHelpServiceLabelInfo, 'providerType'> & {
  /** 基礎研修課程修了者等により行われる場合 */
  isBeginner: boolean

  /** 重度訪問介護研修修了者による場合 */
  isCareWorkerForPwsd: boolean
}

/**
 * サービス提供を表すラベルを解析するパーサーを生成する.
 */
const createServiceLabelParser = () => {
  const category = patternMatch<DwsServiceCodeCategory>(
    [string('身体'), DwsServiceCodeCategory.physicalCare],
    [string('家事'), DwsServiceCodeCategory.housework],
    [string('通院1'), DwsServiceCodeCategory.accompanyWithPhysicalCare],
    [string('通院2'), DwsServiceCodeCategory.accompany],
    [string('通院乗降'), DwsServiceCodeCategory.accessibleTaxi]
  )
  const timeframe = patternMatch<Timeframe>(
    [string('日'), Timeframe.daytime],
    [string('早'), Timeframe.morning],
    [string('夜'), Timeframe.night],
    [string('深'), Timeframe.midnight]
  )
  const time = seqObject<Time>(
    { timeframe: string('日跨増深').map(_ => Timeframe.midnight).or(timeframe) },
    { isExtra: option('増') },
    { minutes }
  )
  const buildingType = alt<DwsHomeHelpServiceBuildingType>(
    dot.then(string('建2')).map(_ => DwsHomeHelpServiceBuildingType.over50),
    dot.then(string('建1')).map(_ => DwsHomeHelpServiceBuildingType.over20),
    empty.map(_ => DwsHomeHelpServiceBuildingType.none)
  )
  const rawData = seqObject<RawLabelInfo>(
    { category },
    { isCareWorkerForPwsd: option('重研') },
    { times: time.sepBy(dot) },
    { isBeginner: dotAndOption('基') },
    { isSecondary: dotAndOption('2人') },
    { isPlannedByNovice: dotAndOption('初計') },
    { buildingType },
    eof
  )
  const providerType = (x: RawLabelInfo): DwsHomeHelpServiceProviderType => {
    if (x.isBeginner) {
      return DwsHomeHelpServiceProviderType.beginner
    } else if (x.isCareWorkerForPwsd) {
      return DwsHomeHelpServiceProviderType.careWorkerForPwsd
    } else {
      return DwsHomeHelpServiceProviderType.none
    }
  }
  return rawData.map<HomeHelpServiceLabelInfo>(x => ({
    ...omit(x, ['isBeginner', 'isCareWorkerForPwsd']),
    providerType: providerType(x)
  }))
}

/**
 * 加算関連のラベルを解析するパーサーを生成する.
 */
const createAdditionLabelParser = (pattern: string, category: DwsServiceCodeCategory) => string(pattern)
  .map<HomeHelpServiceLabelInfo>(_ => ({
    category,
    times: [],
    isPlannedByNovice: false,
    isSecondary: false,
    buildingType: DwsHomeHelpServiceBuildingType.none,
    providerType: DwsHomeHelpServiceProviderType.none
  }))

/**
 * サービスコード（居宅介護）の名称（略称）を解析するパーサー.
 */
const parser: Parser<HomeHelpServiceLabelInfo> = alt(
  createServiceLabelParser(),
  createAdditionLabelParser('居介特定事業所加算Ⅰ', DwsServiceCodeCategory.specifiedOfficeAddition1),
  createAdditionLabelParser('居介特定事業所加算Ⅱ', DwsServiceCodeCategory.specifiedOfficeAddition2),
  createAdditionLabelParser('居介特定事業所加算Ⅲ', DwsServiceCodeCategory.specifiedOfficeAddition3),
  createAdditionLabelParser('居介特定事業所加算Ⅳ', DwsServiceCodeCategory.specifiedOfficeAddition4),
  createAdditionLabelParser('居介特地加算', DwsServiceCodeCategory.specifiedAreaAddition),
  createAdditionLabelParser('居介緊急時対応加算（地域生活拠点）', DwsServiceCodeCategory.emergencyAddition2),
  createAdditionLabelParser('居介緊急時対応加算', DwsServiceCodeCategory.emergencyAddition1),
  createAdditionLabelParser('居介喀痰吸引等支援体制加算', DwsServiceCodeCategory.suckingSupportSystemAddition),
  createAdditionLabelParser('居介初回加算', DwsServiceCodeCategory.firstTimeAddition),
  createAdditionLabelParser('居介上限額管理加算', DwsServiceCodeCategory.copayCoordinationAddition),
  createAdditionLabelParser('居介福祉専門職員等連携加算', DwsServiceCodeCategory.welfareSpecialistCooperationAddition),
  createAdditionLabelParser('居介処遇改善加算Ⅰ', DwsServiceCodeCategory.treatmentImprovementAddition1),
  createAdditionLabelParser('居介処遇改善加算Ⅱ', DwsServiceCodeCategory.treatmentImprovementAddition2),
  createAdditionLabelParser('居介処遇改善加算Ⅲ', DwsServiceCodeCategory.treatmentImprovementAddition3),
  createAdditionLabelParser('居介処遇改善加算Ⅳ', DwsServiceCodeCategory.treatmentImprovementAddition4),
  createAdditionLabelParser('居介処遇改善加算Ⅴ', DwsServiceCodeCategory.treatmentImprovementAddition5),
  createAdditionLabelParser('居介処遇改善特別加算', DwsServiceCodeCategory.treatmentImprovementSpecialAddition),
  createAdditionLabelParser('居介特定処遇改善加算Ⅰ', DwsServiceCodeCategory.specifiedTreatmentImprovementAddition1),
  createAdditionLabelParser('居介特定処遇改善加算Ⅱ', DwsServiceCodeCategory.specifiedTreatmentImprovementAddition2),
  createAdditionLabelParser('令和3年9月30日までの上乗せ分（居介）', DwsServiceCodeCategory.covid19PandemicSpecialAddition),
  createAdditionLabelParser('居介同一建物減算1', DwsServiceCodeCategory.bulkServiceSubtraction1),
  createAdditionLabelParser('居介同一建物減算2', DwsServiceCodeCategory.bulkServiceSubtraction2),
  createAdditionLabelParser('居介身体拘束廃止未実施減算', DwsServiceCodeCategory.physicalRestraintSubtraction),
  createAdditionLabelParser('居介ベースアップ等支援加算', DwsServiceCodeCategory.baseIncreaseSupportAddition)
)

/**
 * サービスコード（居宅介護）の名称（略称）をパースする.
 */
export const parseDwsHomeHelpServiceLabel = (input: string): HomeHelpServiceLabelInfo => parser.tryParse(input)
