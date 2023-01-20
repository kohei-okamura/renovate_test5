/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as fs from 'fs'
import { Readable } from 'stream'
import { createCsvStringifyStream } from './create-csv-stringify-stream'

type CsvValue = boolean | number | string

export const outputCsv = <T> (output: string, rows: T[], callback: (row: T) => CsvValue[]) => {
  const inputStream = Readable.from(rows.map(callback))
  const filterStream = createCsvStringifyStream(',')
  const outputStream = fs.createWriteStream(output)
  inputStream.pipe(filterStream).pipe(outputStream)
}
