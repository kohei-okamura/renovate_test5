/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { ROW_DWS_XLSX_SHEET_HEADER } from '../constants'
import { fileExists } from './file-exists'
import { ParseDwsRowFunction } from './parse-dws-row'
import { readBook, Sheet } from './xlsx'

const SERVICE_DIVISION_CODE_COLUMN_INDEX = 0

/**
 * 障害福祉サービス XLSX の特定シートにおける単位数の列番号を取得する.
 */
export const getScoreColumnIndex = (sheet: Sheet): number => {
  const headerRow = sheet.rows.get(ROW_DWS_XLSX_SHEET_HEADER)
  return headerRow?.columns.findIndex(value => value === '単位数') ?? 0
}

/**
 * サービスコードのエクセルから辞書エントリ（居宅）を生成する.
 */
export const parseDwsXlsx = async <T> (
  source: string,
  division: DwsServiceDivisionCode,
  sheetNamePattern: RegExp,
  parseRow: ParseDwsRowFunction<T>
): Promise<T[]> => {
  if (!(await fileExists(source))) {
    throw new Error(`File not found: ${source}`)
  }
  return readBook(source)
    .getSheets(sheetNamePattern)
    .flatMap(sheet => {
      const scoreColumnIndex = getScoreColumnIndex(sheet)
      return sheet.rows.skip(ROW_DWS_XLSX_SHEET_HEADER + 1).flatMap(row => {
        const serviceDivisionCode = row.get(SERVICE_DIVISION_CODE_COLUMN_INDEX).toString()
        return serviceDivisionCode === division
          ? parseRow(row, scoreColumnIndex)
          : []
      })
    })
    .toArray()
}
