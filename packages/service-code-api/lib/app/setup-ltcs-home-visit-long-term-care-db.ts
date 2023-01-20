/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LTCS_HOME_VISIT_LONG_TERM_CARE_CSV, LtcsVersion, LTCS_VERSIONS } from '../constants'
import { createLtcsHomeVisitLongTermCareDb } from '../utils/create-ltcs-home-visit-long-term-care-db'
import { parseLtcsCsv } from '../utils/parse-ltcs-csv'

const parse = async (version: LtcsVersion) => {
  const rows = await parseLtcsCsv(LTCS_HOME_VISIT_LONG_TERM_CARE_CSV[version])
  return await createLtcsHomeVisitLongTermCareDb({ version, rows })
}

export const setupLtcsHomeVisitLongTermCareDb = async () => await Promise.all(LTCS_VERSIONS.map(parse))
