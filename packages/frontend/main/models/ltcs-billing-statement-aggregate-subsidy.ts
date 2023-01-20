/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 介護保険サービス：明細書：集計：公費.
 */
export type LtcsBillingStatementAggregateSubsidy = Readonly<{
  /** 単位数合計 */
  totalScore: number

  /** 請求額 */
  claimAmount: number

  /** 本人負担額 */
  copayAmount: number
}>
