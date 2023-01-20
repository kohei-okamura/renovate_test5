/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UnionToTuple } from '@zinger/helpers/types'
import path from 'path'

export const ASSETS = path.resolve(__dirname, '..', 'assets')
export const RESOURCES = path.resolve(__dirname, '..', 'resources')

export type LtcsVersion = 201910 | 202104 | 202210

export type DwsVersion = 201910 | 202104 | 202210

export const DWS_VERSIONS: UnionToTuple<DwsVersion> = [201910, 202104, 202210]
export const LTCS_VERSIONS: UnionToTuple<LtcsVersion> = [201910, 202104, 202210]

export const DWS_HOME_HELP_SERVICE_DDL = path.join(RESOURCES, 'dws-home-help-service.sql')
export const DWS_HOME_HELP_SERVICE_XLSX: Record<DwsVersion, [string, number]> = {
  201910: [path.join(RESOURCES, 'dws-201910-source.xlsx'), 2019],
  202104: [path.join(RESOURCES, 'dws-202104-source.xlsx'), 2021],
  202210: [path.join(RESOURCES, 'dws-202210-source.xlsx'), 2022]
}
export const DWS_VISITING_CARE_FOR_PWSD_SERVICE_DDL = path.join(RESOURCES, 'dws-visiting-care-for-pwsd-service.sql')
export const DWS_VISITING_CARE_FOR_PWSD_SERVICE_XLSX: Record<DwsVersion, [string]> = {
  201910: [path.join(RESOURCES, 'dws-201910-source.xlsx')],
  202104: [path.join(RESOURCES, 'dws-202104-source.xlsx')],
  202210: [path.join(RESOURCES, 'dws-202210-source.xlsx')]
}
export const LTCS_HOME_VISIT_LONG_TERM_CARE_DDL = path.join(RESOURCES, 'ltcs-home-visit-long-term-care.sql')
export const LTCS_HOME_VISIT_LONG_TERM_CARE_CSV: Record<LtcsVersion, string> = {
  201910: path.join(RESOURCES, 'ltcs-201910-source.csv'),
  202104: path.join(RESOURCES, 'ltcs-202104-source.csv'),
  202210: path.join(RESOURCES, 'ltcs-202210-source.csv')
}
export const DWS_HOME_HELP_SERVICE_DB_DIR = process.env.SERVICE_CODE_API_ASSETS ?? path.join(ASSETS, 'dws11')
export const DWS_HOME_HELP_SERVICE_DB: Record<DwsVersion, string> = {
  201910: path.join(DWS_HOME_HELP_SERVICE_DB_DIR, 'dws-home-help-service-201910.db'),
  202104: path.join(DWS_HOME_HELP_SERVICE_DB_DIR, 'dws-home-help-service-202104.db'),
  202210: path.join(DWS_HOME_HELP_SERVICE_DB_DIR, 'dws-home-help-service-202210.db')
}
export const DWS_VISITING_CARE_FOR_PWSD_DB_DIR = process.env.SERVICE_CODE_API_ASSETS ?? path.join(ASSETS, 'dws12')
export const DWS_VISITING_CARE_FOR_PWSD_DB: Record<DwsVersion, string> = {
  201910: path.join(DWS_VISITING_CARE_FOR_PWSD_DB_DIR, 'dws-visiting-care-for-pwsd-201910.db'),
  202104: path.join(DWS_VISITING_CARE_FOR_PWSD_DB_DIR, 'dws-visiting-care-for-pwsd-202104.db'),
  202210: path.join(DWS_VISITING_CARE_FOR_PWSD_DB_DIR, 'dws-visiting-care-for-pwsd-202210.db')
}
export const LTCS_HOME_VISIT_LONG_TERM_CARE_DB_DIR = process.env.SERVICE_CODE_API_ASSETS ?? path.join(ASSETS, 'ltcs11')
export const LTCS_HOME_VISIT_LONG_TERM_CARE_DB: Record<LtcsVersion, string> = {
  201910: path.join(LTCS_HOME_VISIT_LONG_TERM_CARE_DB_DIR, 'ltcs-home-visit-long-term-care-201910.db'),
  202104: path.join(LTCS_HOME_VISIT_LONG_TERM_CARE_DB_DIR, 'ltcs-home-visit-long-term-care-202104.db'),
  202210: path.join(LTCS_HOME_VISIT_LONG_TERM_CARE_DB_DIR, 'ltcs-home-visit-long-term-care-202210.db')
}

export const ROW_DWS_XLSX_SHEET_HEADER = 5
