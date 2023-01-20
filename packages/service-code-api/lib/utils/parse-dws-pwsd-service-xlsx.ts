/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { DwsVisitingCareForPwsdDictionaryEntry } from '../models/dws-visiting-care-for-pwsd-dictionary-entry'
import { range } from '../models/range'
import { parseDwsPwsdServiceLabel } from './parse-dws-pwsd-service-label'
import { parseDwsRow } from './parse-dws-row'
import { parseDwsXlsx } from './parse-dws-xlsx'

type ParseDwsPwsdServiceXlsx = (source: string) => Promise<DwsVisitingCareForPwsdDictionaryEntry[]>

const SHEET_NAME_PATTERN = /^2重度訪問/

/**
 * サービスコードのエクセルから辞書エントリ（重訪）を生成する.
 */
export const parseDwsPwsdServiceXlsx: ParseDwsPwsdServiceXlsx = async source => await parseDwsXlsx(
  source,
  DwsServiceDivisionCode.visitingCareForPwsd,
  SHEET_NAME_PATTERN,
  parseDwsRow(data => {
    const info = parseDwsPwsdServiceLabel(data.label)
    const isOutingSupport = info.category === DwsServiceCodeCategory.outingSupportForPwsd

    const durationStart = isOutingSupport
      ? (info.minutes === 0 ? 0 : (info.minutes === 60 ? 60 : (info.minutes - (info.minutes === 240 ? 60 : 30))))
      : (info.minutes === 0 ? 0 : (info.minutes === 60 ? 60 : (info.minutes - (info.minutes <= 240 ? 30 : 240))))

    const durationEnd = isOutingSupport
      ? (info.minutes === 240 ? 9999 : info.minutes)
      : (info.minutes === 1440 ? 9999 : info.minutes)

    const unit = isOutingSupport
      ? (info.minutes === 0 ? 0 : (info.minutes !== 60 && info.minutes !== 240 ? 30 : 60))
      : (info.minutes === 0 ? 0 : (info.minutes !== 60 ? 30 : 60))

    const entry: DwsVisitingCareForPwsdDictionaryEntry = {
      serviceCode: data.serviceCode,
      name: data.label,
      category: info.category,
      isSecondary: info.isSecondary,
      isCoaching: info.isCoaching,
      isHospitalized: info.isHospitalized,
      isLongHospitalized: info.isLongHospitalized,
      score: +data.score,
      timeframe: info.timeframe,
      duration: range(durationStart, durationEnd),
      unit
    }
    return [entry]
  })
)
