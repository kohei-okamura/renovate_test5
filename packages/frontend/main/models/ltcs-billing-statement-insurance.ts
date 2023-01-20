/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 介護保険サービス：明細書：保険請求内容.
 */
export type LtcsBillingStatementInsurance = Readonly<{
  /** 給付率 */
  benefitRate: number

  /** サービス単位数 */
  totalScore: number

  /** 請求額 */
  claimAmount: number

  /** 利用者負担額 */
  copayAmount: number
}>
