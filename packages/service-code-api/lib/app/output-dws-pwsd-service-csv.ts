/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DWS_VISITING_CARE_FOR_PWSD_SERVICE_XLSX, DwsVersion } from '../constants'
import { outputCsv } from '../utils/output-csv'
import { parseDwsPwsdServiceXlsx } from '../utils/parse-dws-pwsd-service-xlsx'

export const outputDwsPwsdServiceCsv = async (version: DwsVersion, output: string) => outputCsv(
  output,
  await parseDwsPwsdServiceXlsx(...DWS_VISITING_CARE_FOR_PWSD_SERVICE_XLSX[version]),
  row => [
    row.serviceCode,
    row.serviceCode.substring(0, 2),
    row.serviceCode.substring(2, 6),
    row.name,
    row.category,
    row.isSecondary,
    row.isCoaching,
    row.isHospitalized,
    row.isLongHospitalized,
    row.score,
    row.timeframe,
    row.duration.start,
    row.duration.end,
    row.unit
  ]
)
