/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

const BACKLOG_API_KEY = 'BACKLOG_API_KEY' as const
const BACKLOG_PROJECT_KEY = 'BACKLOG_PROJECT_KEY' as const
const BACKLOG_SPACE_URL = 'BACKLOG_SPACE_URL' as const

const SHEET_TASKS = 'Tasks'
const SHEET_PBI = 'PBI'
const SHEET_SETTINGS = 'Settings'
const SHEET_MILESTONES = 'Milestones'
const SHEET_ISSUE_TYPES = 'Types'
const SHEET_PRIORITIES = 'Priorities'

const MILESTONE = 'MILESTONE' as const
const PBI_ISSUE_TYPE = 'PBI_ISSUE_TYPE' as const
const TASK_ISSUE_TYPE = 'TASK_ISSUE_TYPE' as const
const PRIORITY = 'PRIORITY' as const

type SettingKey = typeof MILESTONE | typeof PBI_ISSUE_TYPE | typeof TASK_ISSUE_TYPE | typeof PRIORITY
type Settings = Record<SettingKey, number>
type Sheet = GoogleAppsScript.Spreadsheet.Sheet
type Spreadsheet = GoogleAppsScript.Spreadsheet.Spreadsheet

// ---------------------------------------------------------------------------------------------------------------------
// Utility Functions
// ---------------------------------------------------------------------------------------------------------------------

/**
 * UI を用いる処理を実行する.
 */
const withUi = <T> (f: (ui: GoogleAppsScript.Base.Ui) => T): T => {
  const ui = SpreadsheetApp.getUi()
  return f(ui)
}

/**
 * UserPropertiesManager を用いる処理を実行する.
 */
const withUserProperties = <T> (f: (manager: UserPropertiesManager.Manager) => T): T => {
  const store = PropertiesService.getUserProperties()
  const manager = UserPropertiesManager.createManager(store)
  return f(manager)
}

/**
 * 設定値の一覧を取得する.
 */

/**
 * Backlog クライアントを生成する.
 */
const createBacklogClient = (): BacklogApp.Client => withUserProperties(store => {
  const apiKey = store.getSafety(BACKLOG_API_KEY, 'Backlog API キー')
  const projectKey = store.getSafety(BACKLOG_PROJECT_KEY, 'Backlog プロジェクトキー')
  const url = store.getSafety(BACKLOG_SPACE_URL, 'Backlog スペース URL')
  return BacklogApp.createClient({ apiKey, projectKey, url })
})

/**
 * ユーザープロパティの設定・表示関数を生成する.
 */
const createPropertyMenu = (key: string, title: string) => [
  // get function
  () => withUserProperties(manager => withUi(ui => manager.prompt(ui, key, title))),
  // set function
  () => withUserProperties(manager => withUi(ui => manager.show(ui, key, title)))
]

/**
 * 名前を指定してシートを取得する.
 */
const getSheetByName = (spreadsheet: Spreadsheet, name: string): Sheet => {
  const sheet = spreadsheet.getSheetByName(name)
  if (sheet === null) {
    throw new Error(`Sheet not found: ${name}`)
  }
  return sheet
}

/**
 * 設定を取得する.
 */
const getSettings = (spreadsheet: Spreadsheet): Settings => {
  const sheet = getSheetByName(spreadsheet, SHEET_SETTINGS)
  const entries = sheet.getRange('A:C').getValues().map(x => [x[0], x[2]])
  return Object.fromEntries(entries)
}

/**
 * シートを更新する.
 */
const updateSheet = (spreadsheet: Spreadsheet, name: string, numColumns: number, values: any[][]): void => {
  getSheetByName(spreadsheet, name).getRange(1, 1, values.length, numColumns).setValues(values)
}

const buildDescription = (summary: string, content: string, definitions: string, links: string): string => {
  const lines = [
    '## 内容',
    (content || summary),
    '',
    '## 完了条件',
    definitions,
    '',
    ...(links === '' ? [] : ['## 関連課題・URL 等', links])
  ]
  return lines.join('\n').trim()
}

// ---------------------------------------------------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------------------------------------------------

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const oauth = () => withUi(ui => ui.alert('OAuth 認証済み'))

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const [setBacklogApiKey, getBacklogApiKey] = createPropertyMenu(BACKLOG_API_KEY, 'Backlog API キー')

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const [setBacklogProjectKey, getBacklogProjectKey] = createPropertyMenu(BACKLOG_PROJECT_KEY, 'Backlog プロジェクトキー')

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const [setBacklogSpaceUrl, getBacklogSpaceUrl] = createPropertyMenu(BACKLOG_SPACE_URL, 'Backlog スペース URL')

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const updateBacklogSheets = (): void => {
  const backlog = createBacklogClient()
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet()
  updateSheet(spreadsheet, SHEET_MILESTONES, 2, backlog.getMilestones().map(x => [x.name, x.id]))
  updateSheet(spreadsheet, SHEET_ISSUE_TYPES, 2, backlog.getIssueTypes().map(x => [x.name, x.id]))
  updateSheet(spreadsheet, SHEET_PRIORITIES, 2, backlog.getPriorities().map(x => [x.name, x.id]))
  withUi(ui => ui.alert('更新が完了しました'))
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const updateProductBacklogItems = (): void => {
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet()
  const settings = getSettings(spreadsheet)
  const backlog = createBacklogClient()
  const issues = backlog.getIssues({
    issueTypeId: [settings.PBI_ISSUE_TYPE],
    milestoneId: [settings.MILESTONE],
    sort: 'created',
    order: 'asc'
  })
  updateSheet(spreadsheet, SHEET_PBI, 3, issues.map(x => [x.issueKey, x.summary, x.id]))
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const create = (): void => {
  const backlog = createBacklogClient()
  const project = backlog.getProject()
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet()
  const settings = getSettings(spreadsheet)
  const sheet = getSheetByName(spreadsheet, SHEET_TASKS)
  const values = sheet.getRange(2, 3, sheet.getLastRow() - 1, 6).getValues()

  // 課題キーが設定されていない行のみ Backlog に課題を登録し、その件数を算出する.
  const count = values.reduce(
    (z, [parentIssueId, summary, content, definitions, links, key], offset) => {
      if (key) {
        return z
      } else {
        const { issueKey } = backlog.createIssue({
          parentIssueId,
          summary,
          projectId: +project.id!,
          description: buildDescription(summary, content, definitions, links),
          priorityId: settings.PRIORITY,
          issueTypeId: settings.TASK_ISSUE_TYPE,
          milestoneId: [settings.MILESTONE]
        })
        const formula = `=HYPERLINK("https://eustylelab.backlog.com/view/${issueKey}", "${issueKey}")`
        sheet.getRange(2 + offset, 8).setFormula(formula)
        return z + 1
      }
    },
    0
  )
  withUi(ui => ui.alert(`${count}件の課題を登録しました`))
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const onOpen = ({ source }: GoogleAppsScript.Events.SheetsOnOpen): void => source.addMenu('操作', [
  { name: 'OAuth 認証', functionName: 'oauth' },
  null,
  { name: 'Backlog API キーを設定', functionName: 'setBacklogApiKey' },
  { name: 'Backlog API キーを表示', functionName: 'getBacklogApiKey' },
  { name: 'Backlog プロジェクトキーを設定', functionName: 'setBacklogProjectKey' },
  { name: 'Backlog プロジェクトキーを表示', functionName: 'getBacklogProjectKey' },
  { name: 'Backlog スペース URL を設定', functionName: 'setBacklogSpaceUrl' },
  { name: 'Backlog スペース URL を表示', functionName: 'getBacklogSpaceUrl' },
  null,
  { name: 'Backlog 情報を更新（取得）', functionName: 'updateBacklogSheets' },
  { name: 'PBI の一覧を更新（取得）', functionName: 'updateProductBacklogItems' },
  { name: '課題を一括登録', functionName: 'create' }
])
