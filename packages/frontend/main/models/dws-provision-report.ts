/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { ContractId } from '~/models/contract'
import { DateLike } from '~/models/date'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'

/**
 * 予実 ID.
 */
export type DwsProvisionReportId = number

/**
 * 障害福祉サービス：予実.
 */
export type DwsProvisionReport = Readonly<{
  /** 予実 ID */
  id: DwsProvisionReportId

  /** 利用者 ID */
  userId: UserId

  /** 事業所 ID */
  officeId: OfficeId

  /** 契約 ID */
  contractId: ContractId

  /** サービス提供年月 */
  providedIn: DateLike

  /** 予定 */
  plans: DwsProvisionReportItem[]

  /** 実績 */
  results: DwsProvisionReportItem[]

  /** 状態 */
  status: DwsProvisionReportStatus

  /** 確定日時 */
  fixedAt: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
