/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LTCS_HOME_VISIT_LONG_TERM_CARE_CSV, LtcsVersion } from '../constants'
import { outputCsv } from '../utils/output-csv'
import { parseLtcsCsv } from '../utils/parse-ltcs-csv'

export const outputLtcsHomeVisitLongTermCareCsv = async (version: LtcsVersion, output: string) => outputCsv(
  output,
  await parseLtcsCsv(LTCS_HOME_VISIT_LONG_TERM_CARE_CSV[version]),
  row => [
    row.serviceCode,
    row.serviceCode.substring(0, 2),
    row.serviceCode.substring(2, 6),
    row.name,
    row.category,
    row.headcount,
    row.compositionType,
    row.specifiedOfficeAddition,
    row.noteRequirement,
    row.isLimited,
    row.isBulkSubtractionTarget,
    row.isSymbioticSubtractionTarget,
    row.score.value,
    row.score.calcType,
    row.score.calcCycle,
    row.extraScore.isAvailable,
    row.extraScore.baseMinutes,
    row.extraScore.unitScore,
    row.extraScore.unitMinutes,
    row.extraScore.specifiedOfficeAdditionCoefficient,
    row.extraScore.timeframeAdditionCoefficient,
    row.timeframe,
    row.totalMinutes.start,
    row.totalMinutes.end,
    row.physicalMinutes.start,
    row.physicalMinutes.end,
    row.houseworkMinutes.start,
    row.houseworkMinutes.end
  ]
)
