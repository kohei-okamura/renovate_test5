/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 介護保険サービス：明細書：公費請求内容.
 */
export type LtcsBillingStatementSubsidy = Readonly<{
  /** 負担者番号 */
  defrayerNumber: string

  /** 受給者番号 */
  recipientNumber: string

  /** 給付率 */
  benefitRate: number

  /** 単位数合計 */
  totalScore: number

  /** 請求額 */
  claimAmount: number

  /** 利用者負担額 */
  copayAmount: number
}>
