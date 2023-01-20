/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DWS_VERSIONS, DWS_VISITING_CARE_FOR_PWSD_SERVICE_XLSX, DwsVersion } from '../constants'
import { createDwsVisitingCareForPwsdDb } from '../utils/create-dws-visiting-care-for-pwsd-db'
import { parseDwsPwsdServiceXlsx } from '../utils/parse-dws-pwsd-service-xlsx'

const parse = async (version: DwsVersion) => {
  const rows = await parseDwsPwsdServiceXlsx(...DWS_VISITING_CARE_FOR_PWSD_SERVICE_XLSX[version])
  return await createDwsVisitingCareForPwsdDb({ version, rows })
}

export const setupDwsVisitingCareForPwsdDb = async () => await Promise.all(DWS_VERSIONS.map(parse))
