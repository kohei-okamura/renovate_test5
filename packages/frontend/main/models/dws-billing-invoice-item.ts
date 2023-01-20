/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

import { DwsBillingPaymentCategory } from '@zinger/enums/lib/dws-billing-payment-category'

/**
 * 障害福祉サービス請求書：明細.
 */
export type DwsBillingInvoiceItem = Readonly<{
  /** 給付種別 */
  paymentCategory: DwsBillingPaymentCategory

  /** サービス種類コード */
  serviceDivisionCode: string

  /** 件数 */
  subtotalCount: number

  /** 単位数 */
  subtotalScore: number

  /** 費用合計 */
  subtotalFee: number

  /** 給付費請求額 */
  subtotalBenefit: number

  /** 利用者負担額 */
  subtotalCopay?: number

  /** 自治体助成額 */
  subtotalSubsidy?: number
}>
