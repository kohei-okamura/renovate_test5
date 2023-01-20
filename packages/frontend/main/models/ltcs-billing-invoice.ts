/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DefrayerCategory } from '@zinger/enums/lib/defrayer-category'
import { DateLike } from '~/models/date'
import { LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundleId } from '~/models/ltcs-billing-bundle'

/**
 * 請求書 ID.
 */
export type LtcsBillingInvoiceId = number

/**
 * 介護保険サービス：請求書.
 */
export type LtcsBillingInvoice = Readonly<{
  /** 請求書 ID */
  id: LtcsBillingInvoiceId

  /** 請求 ID */
  billingId: LtcsBillingId

  /** 請求単位 ID */
  bundleId: LtcsBillingBundleId

  /** 公費フラグ */
  isSubsidy: boolean

  /** 公費制度（法別番号） */
  defrayerCategory: DefrayerCategory | undefined

  /** サービス費用：件数 */
  statementCount: number

  /** サービス費用：単位数 */
  totalScore: number

  /** サービス費用：費用合計 */
  totalFee: number

  /** サービス費用：保険請求額 */
  insuranceAmount: number

  /** サービス費用：公費請求額 */
  subsidyAmount: number

  /** サービス費用：利用者負担 */
  copayAmount: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
