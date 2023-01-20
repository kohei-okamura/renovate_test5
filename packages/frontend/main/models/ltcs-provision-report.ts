/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  HomeVisitLongTermCareSpecifiedOfficeAddition
} from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { LtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { LtcsProvisionReportStatus } from '@zinger/enums/lib/ltcs-provision-report-status'
import {
  LtcsSpecifiedTreatmentImprovementAddition
} from '@zinger/enums/lib/ltcs-specified-treatment-improvement-addition'
import { LtcsTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-treatment-improvement-addition'
import { ContractId } from '~/models/contract'
import { DateLike } from '~/models/date'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { LtcsProvisionReportOverScore } from '~/models/ltcs-provision-report-over-score'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'

/**
 * 介護保険サービス：予実 ID.
 */
export type LtcsProvisionReportId = number

/**
 * 介護保険サービス：予実.
 */
export type LtcsProvisionReport = Readonly<{
  /** 予実 ID */
  id: LtcsProvisionReportId

  /** 利用者 ID */
  userId: UserId

  /** 事業所 ID */
  officeId: OfficeId

  /** 契約 ID */
  contractId: ContractId

  /** サービス提供年月 */
  providedIn: DateLike

  /** サービス情報 */
  entries: LtcsProvisionReportEntry[]

  /** 特定事業所加算 */
  specifiedOfficeAddition: HomeVisitLongTermCareSpecifiedOfficeAddition

  /** 処遇改善加算 */
  treatmentImprovementAddition: LtcsTreatmentImprovementAddition

  /** 特定処遇改善加算 */
  specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition

  /** ベースアップ等支援加算 */
  baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition

  /** 地域加算 */
  locationAddition: LtcsOfficeLocationAddition

  /** 超過単位（予定） */
  plan: LtcsProvisionReportOverScore

  /** 超過単位（実績） */
  result: LtcsProvisionReportOverScore

  /** 状態 */
  status: LtcsProvisionReportStatus

  /** 確定日時 */
  fixedAt: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
