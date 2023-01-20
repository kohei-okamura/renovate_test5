/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import sqlite3 from 'better-sqlite3'
import { promises as fs } from 'fs'
import { LTCS_HOME_VISIT_LONG_TERM_CARE_DB, LTCS_HOME_VISIT_LONG_TERM_CARE_DDL, LtcsVersion } from '../constants'
import { LtcsHomeVisitLongTermCareDictionaryEntry } from '../models/ltcs-home-visit-long-term-care-dictionary-entry'
import { createTable } from './create-table'
import { use } from './use'

type Params = {
  version: LtcsVersion
  rows: LtcsHomeVisitLongTermCareDictionaryEntry[]
}

const initDatabase = async (version: LtcsVersion): Promise<void> => {
  const path = LTCS_HOME_VISIT_LONG_TERM_CARE_DB[version]
  await (await fs.open(path, 'w')).close()
  await fs.truncate(path)
}

const insertRows = (db: sqlite3.Database, rows: LtcsHomeVisitLongTermCareDictionaryEntry[]) => {
  const placeholders = Array.from(Array(26), () => '?').join(',')
  const query = db.prepare(`INSERT INTO main VALUES (${placeholders})`)
  db.transaction(() => rows.forEach(row => query.run(
    row.serviceCode,
    row.name,
    row.category,
    row.headcount,
    row.compositionType,
    row.specifiedOfficeAddition,
    row.noteRequirement,
    row.isLimited ? 1 : 0,
    row.isBulkSubtractionTarget ? 1 : 0,
    row.isSymbioticSubtractionTarget ? 1 : 0,
    row.score.calcCycle,
    row.score.calcType,
    row.score.value,
    row.extraScore.baseMinutes,
    row.extraScore.isAvailable ? 1 : 0,
    row.extraScore.specifiedOfficeAdditionCoefficient,
    row.extraScore.timeframeAdditionCoefficient,
    row.extraScore.unitMinutes,
    row.extraScore.unitScore,
    row.timeframe,
    row.totalMinutes.start,
    row.totalMinutes.end,
    row.physicalMinutes.start,
    row.physicalMinutes.end,
    row.houseworkMinutes.start,
    row.houseworkMinutes.end
  )))()
}

export const createLtcsHomeVisitLongTermCareDb = async ({ version, rows }: Params): Promise<number> => {
  await initDatabase(version)
  await use(sqlite3(LTCS_HOME_VISIT_LONG_TERM_CARE_DB[version]))(async db => {
    await createTable(db, LTCS_HOME_VISIT_LONG_TERM_CARE_DDL)
    insertRows(db, rows)
  })
  return rows.length
}
