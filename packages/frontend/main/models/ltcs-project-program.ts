/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DayOfWeek } from '@zinger/enums/lib/day-of-week'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { Recurrence } from '@zinger/enums/lib/recurrence'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { LtcsProjectAmount } from '~/models/ltcs-project-amount'
import { LtcsProjectContent } from '~/models/ltcs-project-content'
import { OwnExpenseProgramId } from '~/models/own-expense-program'
import { TimeRange } from '~/models/range'

/**
 * 介護保険サービス：計画：週間サービス計画.
 */
export type LtcsProjectProgram = Readonly<{
  /** 週間サービス計画番号 */
  programIndex: number

  /** サービス区分 */
  category: LtcsProjectServiceCategory

  /** 繰り返し周期 */
  recurrence: Recurrence

  /** 曜日 */
  dayOfWeeks: DayOfWeek[]

  /** 時間帯 */
  slot: TimeRange

  /** 算定時間帯 */
  timeframe: Timeframe

  /** サービス提供量 */
  amounts: LtcsProjectAmount[]

  /** 提供人数 */
  headcount: number

  /** 自費サービス情報 ID */
  ownExpenseProgramId: OwnExpenseProgramId | undefined

  /** サービスコード */
  serviceCode: string

  /** サービスオプション */
  options: ServiceOption[]

  /** サービス詳細 */
  contents: LtcsProjectContent[]

  /** 備考 */
  note: string
}>
