/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import sqlite3 from 'better-sqlite3'
import { promises as fs } from 'fs'
import {
  DWS_VISITING_CARE_FOR_PWSD_DB,
  DWS_VISITING_CARE_FOR_PWSD_SERVICE_DDL,
  DwsVersion
} from '../constants'
import { DwsVisitingCareForPwsdDictionaryEntry } from '../models/dws-visiting-care-for-pwsd-dictionary-entry'
import { createTable } from './create-table'
import { use } from './use'

type Params = {
  version: DwsVersion
  rows: DwsVisitingCareForPwsdDictionaryEntry[]
}

const initDatabase = async (version: DwsVersion): Promise<void> => {
  const path = DWS_VISITING_CARE_FOR_PWSD_DB[version]
  await (await fs.open(path, 'w')).close()
  await fs.truncate(path)
}

const insertRows = (db: sqlite3.Database, rows: DwsVisitingCareForPwsdDictionaryEntry[]) => {
  const placeholders = Array.from(Array(12), () => '?').join(',')
  const query = db.prepare(`INSERT INTO main VALUES (${placeholders})`)
  db.transaction(() => rows.forEach(row => query.run(
    row.serviceCode,
    row.name,
    row.category,
    row.isSecondary ? 1 : 0,
    row.isCoaching ? 1 : 0,
    row.isHospitalized ? 1 : 0,
    row.isLongHospitalized ? 1 : 0,
    row.score,
    row.timeframe,
    row.duration.start,
    row.duration.end,
    row.unit
  )))()
}

export const createDwsVisitingCareForPwsdDb = async ({ version, rows }: Params): Promise<number> => {
  await initDatabase(version)
  await use(sqlite3(DWS_VISITING_CARE_FOR_PWSD_DB[version]))(async db => {
    await createTable(db, DWS_VISITING_CARE_FOR_PWSD_SERVICE_DDL)
    insertRows(db, rows)
  })
  return rows.length
}
