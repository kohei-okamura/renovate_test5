/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingInvoiceItem } from '~/models/dws-billing-invoice-item'

/**
 * 請求書 ID.
 */
export type DwsBillingInvoiceId = number

/**
 * 障害福祉サービス：請求書.
 */
export type DwsBillingInvoice = Readonly<{
  /** 請求書ID */
  id: DwsBillingInvoiceId

  /** 請求金額 */
  claimAmount: number

  /** 小計：介護給付費等・特別介護給付費等 */
  dwsPayment: {
    /** 件数 */
    subtotalDetailCount: number

    /** 単位数 */
    subtotalScore: number

    /** 費用合計 */
    subtotalFee: number

    /** 給付費請求額 */
    subtotalBenefit: number

    /** 利用者負担額 */
    subtotalCopay: number

    /** 自治体助成額 */
    subtotalSubsidy: number
  }

  /** 小計：特定障害者特別給付費・高額障害福祉サービス費 */
  highCostDwsPayment: {
    /** 件数 */
    subtotalDetailCount: number

    /** 費用合計 */
    subtotalFee: number

    /** 給付費請求額 */
    subtotalBenefit: number
  }

  /** 合計：件数 */
  totalCount: number

  /** 合計：単位数 */
  totalScore: number

  /** 合計：費用合計 */
  totalFee: number

  /** 合計：給付費請求額 */
  totalBenefit: number

  /** 合計：利用者負担額 */
  totalCopay: number

  /** 合計：自治体助成額 */
  totalSubsidy: number

  /** 明細 */
  items: DwsBillingInvoiceItem[]
}>
