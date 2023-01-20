/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 介護保険サービス：明細書：集計：保険.
 */
export type LtcsBillingStatementAggregateInsurance = Readonly<{
  /** 単位数合計 */
  totalScore: number

  /** 単位数単価 */
  unitCost: number

  /** 請求額 */
  claimAmount: number

  /** 利用者負担額 */
  copayAmount: number
}>
