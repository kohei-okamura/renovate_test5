/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DWS_HOME_HELP_SERVICE_XLSX, DWS_VERSIONS, DwsVersion } from '../constants'
import { createDwsHomeHelpServiceDb } from '../utils/create-dws-home-help-service-db'
import { parseDwsHomeHelpServiceXlsx } from '../utils/parse-dws-home-help-service-xlsx'

const parse = async (version: DwsVersion) => {
  const rows = await parseDwsHomeHelpServiceXlsx(...DWS_HOME_HELP_SERVICE_XLSX[version])
  return await createDwsHomeHelpServiceDb({ version, rows })
}

export const setupDwsHomeHelpServiceDb = async () => await Promise.all(DWS_VERSIONS.map(parse))
