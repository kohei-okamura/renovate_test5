/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import sqlite3 from 'better-sqlite3'
import { promises as fs } from 'fs'
import { fileExists } from './file-exists'

export const createTable = async (db: sqlite3.Database, ddlFile: string): Promise<void> => {
  if (await fileExists(ddlFile)) {
    const ddl = await fs.readFile(ddlFile, { encoding: 'utf-8' })
    db.exec(ddl)
  } else {
    throw new Error(`Schema not found: ${ddlFile}`)
  }
}
