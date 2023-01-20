/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DayOfWeek } from '@zinger/enums/lib/day-of-week'
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { Recurrence } from '@zinger/enums/lib/recurrence'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { DwsProjectContent } from '~/models/dws-project-content'
import { OwnExpenseProgramId } from '~/models/own-expense-program'
import { TimeRange } from '~/models/range'

/**
 * 障害福祉サービス：計画：週間サービス計画.
 */
export type DwsProjectProgram = Readonly<{
  /** 週間サービス計画番号 */
  summaryIndex: number

  /** サービス区分 */
  category: DwsProjectServiceCategory

  /** 繰り返し周期 */
  recurrence: Recurrence

  /** 曜日 */
  dayOfWeeks: DayOfWeek[]

  /** 時間帯 */
  slot: TimeRange

  /** 提供人数 */
  headcount: number

  /** 自費サービス情報 ID. */
  ownExpenseProgramId: OwnExpenseProgramId | undefined

  /** サービスオプション. */
  options: ServiceOption[]

  /** サービス詳細 */
  contents: DwsProjectContent[]

  /** 備考 */
  note: string
}>
