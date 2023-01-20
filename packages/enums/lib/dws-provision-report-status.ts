/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  notCreated: 1,
  inProgress: 2,
  fixed: 3
} as const

/**
 * 障害福祉サービス：予実：状態.
 */
export type DwsProvisionReportStatus = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsProvisionReportStatus = createEnumerable($$, [
  [$$.notCreated, '未作成'],
  [$$.inProgress, '作成中'],
  [$$.fixed, '確定済']
])

export const resolveDwsProvisionReportStatus = DwsProvisionReportStatus.resolve
