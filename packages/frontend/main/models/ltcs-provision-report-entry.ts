/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { DateLike } from '~/models/date'
import { LtcsProjectAmount } from '~/models/ltcs-project-amount'
import { OwnExpenseProgramId } from '~/models/own-expense-program'
import { TimeRange } from '~/models/range'

/**
 * 介護保険サービス：予実：サービス情報.
 */
export type LtcsProvisionReportEntry = Readonly<{
  /** 時間帯 */
  slot: TimeRange

  /** 算定時間帯 */
  timeframe: Timeframe

  /** サービス区分 */
  category: LtcsProjectServiceCategory

  /** サービス提供量 */
  amounts: LtcsProjectAmount[]

  /** 提供人数 */
  headcount: number

  /** 自費サービス情報 ID */
  ownExpenseProgramId: OwnExpenseProgramId | undefined

  /** サービスコード */
  serviceCode: string | undefined

  /** サービスオプション */
  options: ServiceOption[]

  /** 備考 */
  note: string

  /** 予定年月日 */
  plans: DateLike[]

  /** 実績年月日 */
  results: DateLike[]
}>
