/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import sqlite3 from 'better-sqlite3'
import { promises as fs } from 'fs'
import { DWS_HOME_HELP_SERVICE_DB, DWS_HOME_HELP_SERVICE_DDL, DwsVersion } from '../constants'
import { DwsHomeHelpServiceDictionaryEntry } from '../models/dws-home-help-service-dictionary-entry'
import { createTable } from './create-table'
import { use } from './use'

type Params = {
  version: DwsVersion
  rows: DwsHomeHelpServiceDictionaryEntry[]
}

const initDatabase = async (version: DwsVersion): Promise<void> => {
  const path = DWS_HOME_HELP_SERVICE_DB[version]
  await (await fs.open(path, 'w')).close()
  await fs.truncate(path)
}

const insertRows = (db: sqlite3.Database, rows: DwsHomeHelpServiceDictionaryEntry[]) => {
  const placeholders = Array.from(Array(19), () => '?').join(',')
  const query = db.prepare(`INSERT INTO main VALUES (${placeholders})`)
  db.transaction(() => rows.forEach(row => query.run(
    row.serviceCode,
    row.name,
    row.category,
    row.isExtra ? 1 : 0,
    row.isSecondary ? 1 : 0,
    row.providerType,
    row.isPlannedByNovice ? 1 : 0,
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
  )))()
}

export const createDwsHomeHelpServiceDb = async ({ version, rows }: Params): Promise<number> => {
  await initDatabase(version)
  await use(sqlite3(DWS_HOME_HELP_SERVICE_DB[version]))(async db => {
    await createTable(db, DWS_HOME_HELP_SERVICE_DDL)
    insertRows(db, rows)
  })
  return rows.length
}
