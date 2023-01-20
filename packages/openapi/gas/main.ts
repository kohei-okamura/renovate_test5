/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { EntityAttr, EnumEntry, ModelBaseInfo, ModelExtendedInfo } from '../lib/types'

type Sheet = GoogleAppsScript.Spreadsheet.Sheet
type Spreadsheet = GoogleAppsScript.Spreadsheet.Spreadsheet

/**
 * シートを取得する.
 */
const getSheetByName = (spreadsheet: Spreadsheet, name: string): Sheet => {
  const sheet = spreadsheet.getSheetByName(name)
  if (sheet === null) {
    throw new Error(`Sheet not found: ${name}`)
  }
  return sheet
}

/**
 * モデル一覧を取得する.
 */
const getModelList = (spreadsheet: Spreadsheet): ModelBaseInfo[] => {
  const sheet = getSheetByName(spreadsheet, 'モデル一覧')
  return sheet.getRange(2, 1, sheet.getLastRow() - 1, 6).getValues().map(row => ({
    label: row[0] ?? '',
    name: row[1] ?? '',
    type: row[2],
    backend: row[3] !== '✕',
    frontend: row[4] !== '✕',
    namespace: row[5] ?? ''
  }))
}

/**
 * モデルの詳細情報を取得する.
 */
const extend = (spreadsheet: Spreadsheet, x: ModelBaseInfo): ModelExtendedInfo[] => {
  const sheet = spreadsheet.getSheetByName(x.label)
  if (sheet === null) {
    return []
  }
  const rows = sheet.getRange(2, 1, sheet.getLastRow() - 1, 5).getValues()
  if (x.type === 'Enum') {
    const entries = rows
      .map<EnumEntry>(row => ({
        label: row[0] ?? '',
        name: row[1] ?? '',
        value: row[2] === '整数' ? parseInt(row[3] ?? 0) : `${(row[3] ?? '')}`
      }))
      .filter(x => x.value !== '-')
    return [{ ...x, entries }]
  } else {
    const attrs = rows
      .map<EntityAttr>(row => ({
        label: row[0] ?? '',
        name: row[1] ?? '',
        type: row[3] ?? '',
        ...(row[4].includes('読み取り専用') ? { readOnly: true } : {}),
        ...((v: string) => {
          const m = v.match(/(\d+) ?文字/)
          return m ? { minLength: parseInt(m[1]), maxLength: parseInt(m[1]) } : {}
        })(row[4])
      }))
      .filter(x => x.type !== '-')
    return [{ ...x, attrs }]
  }
}

/**
 * JSON 形式の出力を生成する.
 */
const createJsonOutput = <T> (value: T) => {
  const payload = JSON.stringify(value, null, 2)
  const output = ContentService.createTextOutput(payload)
  output.setMimeType(ContentService.MimeType.JSON)
  return output
}

// noinspection JSUnusedLocalSymbols
/**
 * GET リクエスト処理.
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars,no-unused-vars
const doGet = () => {
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet()
  const xs = getModelList(spreadsheet).flatMap(x => extend(spreadsheet, x))
  return createJsonOutput(xs)
}

// noinspection JSUnusedLocalSymbols
/**
 * OAuth 認証.
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars,no-unused-vars
const oauth = () => {
  const ui = SpreadsheetApp.getUi()
  ui.alert('FAQ', 'OAuth 認証済みです。', ui.ButtonSet.OK)
}

// noinspection JSUnusedLocalSymbols
/**
 * 自動リンク.
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars,no-unused-vars
const autoLink = (): void => {
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet()
  const cell = spreadsheet.getActiveCell()
  const name = cell.getValue() as string
  const sheet = spreadsheet.getSheetByName(name)
  if (sheet === null) {
    SpreadsheetApp.getUi().alert(`シート「${name}」が見つかりませんでした`)
  } else {
    const gid = sheet.getSheetId()
    const url = `#gid=${gid}`
    const formula = `=HYPERLINK("${url}", "${name}")`
    cell.setValue(formula)
  }
}

/**
 * 並び替え対象のシート一覧を取得する.
 */
const getSheets = (spreadsheet: Spreadsheet): Sheet[] => {
  const offset = 3
  return spreadsheet.getSheets().slice(offset)
}

/**
 * シート名に対応する「本来の並び順」を Record 形式で取得する.
 */
const getExpectedSheetPositions = (spreadsheet: Spreadsheet): Record<string, number> => {
  const sheet = getSheetByName(spreadsheet, 'モデル一覧')
  const offset = 4
  const entries = sheet.getRange(2, 1, sheet.getLastRow() - 1, 1).getValues().flat().map((x, i) => [x, offset + i])
  return Object.fromEntries(entries)
}

// noinspection JSUnusedLocalSymbols
/**
 * シートを自動で並び替える.
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars,no-unused-vars
const sortSheets = () => {
  console.log('[sortSheets] Start')
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet()
  const sheets = getSheets(spreadsheet)
  const positions = getExpectedSheetPositions(spreadsheet)
  sheets.forEach(sheet => {
    const name = sheet.getName()
    const expected = positions[name]
    const actual = sheet.getIndex()
    if (expected !== undefined && actual !== undefined && expected !== actual) {
      console.log(`[sortSheets] Move ${name} to ${expected} from ${actual}`)
      sheet.activate()
      spreadsheet.moveActiveSheet(expected)
    }
  })
  getSheetByName(spreadsheet, 'モデル一覧').activate()
  SpreadsheetApp.getUi().alert('並べ替えが完了しました。')
  console.log('[sortSheets] Done')
}

// noinspection JSUnusedLocalSymbols
/**
 * スプレッドシートを開いたときの処理.
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars,no-unused-vars
const onOpen = ({ source }: GoogleAppsScript.Events.SheetsOnOpen): void => source.addMenu('操作', [
  { name: 'OAuth 認証', functionName: 'oauth' },
  { name: 'リンク自動設定', functionName: 'autoLink' },
  { name: 'シートを並べ替え', functionName: 'sortSheets' }
])
