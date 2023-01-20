/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsHomeHelpServiceBuildingType } from '@zinger/enums/lib/dws-home-help-service-building-type'
import { DwsHomeHelpServiceProviderType } from '@zinger/enums/lib/dws-home-help-service-provider-type'
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { Range } from 'immutable'
import { DwsHomeHelpServiceDictionaryEntry } from '../models/dws-home-help-service-dictionary-entry'
import { range } from '../models/range'
import { HomeHelpServiceLabelInfo, parseDwsHomeHelpServiceLabel, Time } from './parse-dws-home-help-service-label'
import { parseDwsRow } from './parse-dws-row'
import { parseDwsXlsx } from './parse-dws-xlsx'

type ParseDwsHomeHelpServiceXlsx = (source: string, year: number) => Promise<DwsHomeHelpServiceDictionaryEntry[]>

const SHEET_NAME_PATTERN = /^1居宅介護/

const timeframe = (time: Time | undefined): Timeframe => time?.timeframe ?? Timeframe.unknown

/**
 * 時間数の最小値（min）を算出する.
 *
 * 身体介護および通院等介助（身体を伴う）の場合は 0.5 = 30分きざみ.
 * 家事援助および通院等介助（身体を伴わない）の場合は 0.25 = 15分きざみ.
 *
 * 各条件における最小単位は次の通り。
 *
 * | \          | **単独コード** | **合成コード**                | **増分コード** |
 * |:---------- |:-------------- |:----------------------------- |:-------------- |
 * | 身体       | 0.5 = 30分     | 0.5 = 30分                    | 0.5 = 30分     |
 * | 身体・重研 | 1.0 = 60分     | 0.5 = 30分 または 1.0 = 60分  | 0.5 = 30分     |
 * | 身体でない | 0.5 = 30分     | 0.25 = 15分 または 0.5 = 30分 | 0.25 = 15分    |
 */
const min = (time: Time | undefined, index: number, info: HomeHelpServiceLabelInfo): number => {
  if (time === undefined || time.minutes === 0) {
    return 0
  }
  const isPhysicalCare = info.category === DwsServiceCodeCategory.physicalCare ||
    info.category === DwsServiceCodeCategory.accompanyWithPhysicalCare ||
    info.category === DwsServiceCodeCategory.accompany
  const isHousework = info.category === DwsServiceCodeCategory.housework
  const isPwsd = info.providerType === DwsHomeHelpServiceProviderType.careWorkerForPwsd
  const isComposite = info.times.length > 1
  const isIncrement = info.times.some(x => x.isExtra)
  if (isPhysicalCare && !isPwsd) {
    // 身体
    return time.minutes - 30
  } else if (isPhysicalCare) {
    // 身体・重研
    if (isComposite) {
      return time.minutes === 60 && index === 0 ? 0 : time.minutes - 30
    } else {
      return time.minutes === 60 ? 0 : time.minutes - 30
    }
  } else if (isHousework) {
    // 身体でない
    if (isIncrement) {
      return time.minutes === 15 ? 0 : time.minutes - 15
    } else if (isComposite) {
      return time.minutes === 30 && index === 0 ? 0 : time.minutes - 15
    } else {
      return time.minutes === 30 ? 0 : time.minutes - 15
    }
  } else {
    // 上記以外は時間を扱わない
    return 0
  }
}
const max = (time: Time | undefined): number => time?.minutes ?? 0

/**
 * 時間数情報を取得する.
 */
const getDurations = (info: HomeHelpServiceLabelInfo) => {
  return Range(0, 3)
    .map(i => info.times[i])
    .map((time, index) => ({
      timeframe: timeframe(time),
      range: range(min(time, index, info), max(time))
    }))
    .toArray()
}

/**
 * 指定した時間帯に対応する時間数情報を特定する.
 */
const identifyDuration = (durations: ReturnType<typeof getDurations>, timeframe: Timeframe) => {
  return durations.find(duration => duration.timeframe === timeframe)?.range ?? range(0, 0)
}

/**
 * 日跨ぎかどうかを判定する.
 */
const isDaySpanning = (durations: ReturnType<typeof getDurations>) => {
  return durations[0].timeframe === Timeframe.midnight &&
    durations[1].timeframe === Timeframe.midnight
}

/**
 * サービスコードのエクセルから辞書エントリ（居宅介護）を生成する.
 */
export const parseDwsHomeHelpServiceXlsx: ParseDwsHomeHelpServiceXlsx = async (source, year) => await parseDwsXlsx(
  source,
  DwsServiceDivisionCode.homeHelpService,
  SHEET_NAME_PATTERN,
  parseDwsRow(data => {
    const info = parseDwsHomeHelpServiceLabel(data.label)
    if (year >= 2021 && info.buildingType !== DwsHomeHelpServiceBuildingType.none) {
      return []
    } else {
      const durations = getDurations(info)
      const entry: DwsHomeHelpServiceDictionaryEntry = {
        serviceCode: data.serviceCode,
        name: data.label,
        category: info.category,
        isExtra: info.times.some(x => x.isExtra),
        isSecondary: info.isSecondary,
        providerType: info.providerType,
        isPlannedByNovice: info.isPlannedByNovice,
        buildingType: info.buildingType,
        score: +data.score,
        daytimeDuration: identifyDuration(durations, Timeframe.daytime),
        morningDuration: identifyDuration(durations, Timeframe.morning),
        nightDuration: identifyDuration(durations, Timeframe.night),
        midnightDuration1: identifyDuration(durations, Timeframe.midnight),
        midnightDuration2: isDaySpanning(durations)
          ? durations[1].range
          : range(0, 0)
      }
      return [entry]
    }
  })
)
