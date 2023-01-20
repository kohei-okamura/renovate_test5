/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 介護保険サービス：明細書：明細：公費.
 */
export type LtcsBillingStatementItemSubsidy = Readonly<{
  /** 日数・回数 */
  count: number

  /** サービス単位数 */
  totalScore: number
}>
