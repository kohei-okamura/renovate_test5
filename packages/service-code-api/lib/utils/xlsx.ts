/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Range, Seq } from 'immutable'
import xlsx, { WorkSheet } from 'xlsx'

export type XlsxValue = number | string

export type Row = {
  columns: Seq.Indexed<XlsxValue>
  get (columnIndex: number): XlsxValue
}

export type Sheet = {
  rows: Seq.Indexed<Row>
}

export type Book = {
  getSheets (pattern: RegExp): Seq.Indexed<Sheet>
}

/**
 * 行番号（0〜）および列番号（0〜）から A1 形式のセル番号を取得する.
 *
 * 注：ZZ列までの対応。
 */
const getCellIndex = (rowIndex: number, columnIndex: number): string => {
  return (columnIndex > 25 ? String.fromCharCode(64 + Math.floor(columnIndex / 26)) : '') +
    String.fromCharCode(65 + columnIndex % 26) +
    (rowIndex + 1)
}

/**
 * シート・行番号（0〜）・列数を指定して Row オブジェクトを取得する.
 */
const createRow = (sheet: WorkSheet, rowIndex: number, columnLength: number): Row => {
  const get = (columnIndex: number): XlsxValue => sheet[getCellIndex(rowIndex, columnIndex)]?.v ?? ''
  const columns = Range(0, columnLength).map(get)
  return { columns, get }
}

/**
 * Sheet オブジェクトを生成する.
 */
const createSheet = (sheet: WorkSheet): Sheet => {
  const { r: rowLength, c: columnLength } = xlsx.utils.decode_range(sheet['!ref'] ?? 'A1:A1').e
  const rows = Range(0, rowLength).map(i => createRow(sheet, i, columnLength))
  return {
    rows
  }
}

/**
 * Book オブジェクトを生成する.
 */
export const readBook = (path: string): Book => {
  const workbook = xlsx.readFile(path)
  const getSheets = (pattern: RegExp) => Seq(workbook.SheetNames)
    .filter(name => pattern.test(name))
    .map(name => createSheet(workbook.Sheets[name]))
  return {
    getSheets
  }
}
