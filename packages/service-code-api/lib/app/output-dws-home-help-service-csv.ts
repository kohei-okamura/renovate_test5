/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DWS_HOME_HELP_SERVICE_XLSX, DwsVersion } from '../constants'
import { outputCsv } from '../utils/output-csv'
import { parseDwsHomeHelpServiceXlsx } from '../utils/parse-dws-home-help-service-xlsx'

export const outputDwsHomeHelpServiceCsv = async (version: DwsVersion, output: string) => outputCsv(
  output,
  await parseDwsHomeHelpServiceXlsx(...DWS_HOME_HELP_SERVICE_XLSX[version]),
  row => [
    row.serviceCode,
    row.serviceCode.substring(0, 2),
    row.serviceCode.substring(2, 6),
    row.name,
    row.category,
    row.isExtra,
    row.isSecondary,
    row.providerType,
    row.isPlannedByNovice,
    row.buildingType,
    row.score,
    row.daytimeDuration.start,
    row.daytimeDuration.end,
    row.morningDuration.start,
    row.morningDuration.end,
    row.nightDuration.start,
    row.nightDuration.end,
    row.midnightDuration1.start,
    row.midnightDuration1.end,
    row.midnightDuration2.start,
    row.midnightDuration2.end
  ]
)
