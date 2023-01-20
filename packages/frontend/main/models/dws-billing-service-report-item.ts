/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingServiceReportProviderType } from '@zinger/enums/lib/dws-billing-service-report-provider-type'
import { DwsBillingServiceReportSituation } from '@zinger/enums/lib/dws-billing-service-report-situation'
import { DwsGrantedServiceCode } from '@zinger/enums/lib/dws-granted-service-code'
import { DateLike } from '~/models/date'
import { DwsBillingServiceReportDuration } from '~/models/dws-billing-service-report-duration'

/**
 * サービス提供実績記録票：明細.
 */
export type DwsBillingServiceReportItem = Readonly<{
  /** 提供通番 */
  serialNumber: number

  /** 日付 */
  providedOn: DateLike

  /** サービス内容 */
  serviceType: DwsGrantedServiceCode

  /** ヘルパー資格 */
  providerType: DwsBillingServiceReportProviderType

  /** サービス提供の状況 */
  situation: DwsBillingServiceReportSituation

  /** 予定（計画） */
  plan: DwsBillingServiceReportDuration | undefined

  /** 実績 */
  result: DwsBillingServiceReportDuration | undefined

  /** サービス提供回数 */
  serviceCount: number

  /** 派遣人数 */
  headcount: number

  /** 同行支援 */
  isCoaching: boolean

  /** 初回加算 */
  isFirstTime: boolean

  /** 緊急時対応加算 */
  isEmergency: boolean

  /** 福祉専門職員等連携加算 */
  isWelfareSpecialistCooperation: boolean

  /** 行動障害支援連携加算 */
  isBehavioralDisorderSupportCooperation: boolean

  /** 移動介護緊急時支援加算 */
  isMovingCareSupport: boolean

  /** 運転フラグ */
  isDriving: boolean

  /** 前月からの継続サービス */
  isPreviousMonth: boolean

  /** 備考 */
  note: string
}>
