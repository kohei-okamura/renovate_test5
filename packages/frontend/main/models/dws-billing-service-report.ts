/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingServiceReportFormat } from '@zinger/enums/lib/dws-billing-service-report-format'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { DateLike } from '~/models/date'
import { DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingServiceReportAggregate } from '~/models/dws-billing-service-report-aggregate'
import { DwsBillingServiceReportItem } from '~/models/dws-billing-service-report-item'
import { DwsBillingUser } from '~/models/dws-billing-user'

/**
 * サービス提供実績記録票 ID
 */
export type DwsBillingServiceReportId = number

/**
 * サービス提供実績記録票.
 */
export type DwsBillingServiceReport = Readonly<{
  /** サービス提供実績記録票 ID */
  id: DwsBillingServiceReportId

  /** 請求 ID */
  dwsBillingId: DwsBillingId

  /** 請求単位 ID */
  dwsBillingBundleId: DwsBillingBundleId

  /** 利用者（支給決定者） */
  user: DwsBillingUser

  /** 様式種別番号 */
  format: DwsBillingServiceReportFormat

  /** 合計（計画時間数） */
  plan: DwsBillingServiceReportAggregate

  /** 合計（算定時間数） */
  result: DwsBillingServiceReportAggregate

  /** 提供実績の合計2：緊急時対応加算（回） */
  emergencyCount: number

  /** 提供実績の合計2：初回加算（回） */
  firstTimeCount: number

  /** 提供実績の合計2：福祉専門職員等連携加算（回） */
  welfareSpecialistCooperationCount: number

  /** 提供実績の合計2：行動障害支援連携加算（回） */
  behavioralDisorderSupportCooperationCount: number

  /** 提供実績の合計3：移動介護緊急時支援加算（回） */
  movingCareSupportCount: number

  /** 明細 */
  items: DwsBillingServiceReportItem[]

  /** 状態 */
  status: DwsBillingStatus

  /** 確定日時 */
  fixedAt: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
