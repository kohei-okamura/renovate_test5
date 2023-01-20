/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { DateLike } from '~/models/date'

/**
 * 非同期ジョブ.
 */
export type Job<T = any> = Readonly<{
  /** トークン */
  token: string

  /** 処理結果データ */
  data: T | undefined

  /** 状態 */
  status: JobStatus

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
