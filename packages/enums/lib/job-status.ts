/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  waiting: 1,
  inProgress: 2,
  success: 3,
  failure: 9
} as const

/**
 * 非同期ジョブ：状態.
 */
export type JobStatus = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const JobStatus = createEnumerable($$, [
  [$$.waiting, '待機中'],
  [$$.inProgress, '処理中'],
  [$$.success, '成功'],
  [$$.failure, '失敗']
])

export const resolveJobStatus = JobStatus.resolve
