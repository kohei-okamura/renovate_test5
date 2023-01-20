/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { Range } from '~/models/range'

/**
 * サービス提供実績記録票：明細：算定時間.
 */
export type DwsBillingServiceReportDuration = Readonly<{
  /** 開始時間・終了時間 */
  period: Range<DateLike>

  /** 算定時間数 */
  serviceDurationHours: number

  /** 移動時間数 */
  movingDurationHours: number
}>
