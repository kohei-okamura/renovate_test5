/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { OwnExpenseProgramId } from '~/models/own-expense-program'
import { Schedule } from '~/models/schedule'

/**
 * 障害福祉サービス：予実：要素.
 */
export type DwsProvisionReportItem = Readonly<{
  /** スケジュール */
  schedule: Schedule

  /** サービス区分 */
  category: DwsProjectServiceCategory

  /** 提供人数 */
  headcount: number

  /** 移動介護時間数 */
  movingDurationMinutes: number

  /** 自費サービス情報 ID */
  ownExpenseProgramId: OwnExpenseProgramId | undefined

  /** サービスオプション */
  options: ServiceOption[]

  /** 備考 */
  note: string
}>
