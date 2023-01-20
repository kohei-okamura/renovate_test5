/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Stringifier } from 'csv-stringify'

type CsvDelimiter = ','
type TsvDelimiter = '\t'
type Delimiter = CsvDelimiter | TsvDelimiter

/**
 * 配列を CSV に変換するストリーム.
 */
export const createCsvStringifyStream = (delimiter: Delimiter) => new Stringifier({
  cast: {
    boolean: (x: boolean) => x ? '1' : '0'
  },
  delimiter,
  header: false
})
