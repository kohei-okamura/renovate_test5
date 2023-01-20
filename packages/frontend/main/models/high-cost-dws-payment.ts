/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 障害福祉サービス請求書：小計：特定障害者特別給付費・高額障害福祉サービス費情報.
 */
export type HighCostDwsPayment = Readonly<{
  /** 件数 */
  subtotalDetailCount: number

  /** 費用合計 */
  subtotalFee: number

  /** 給付費請求額 */
  subtotalBenefit: number
}>
