/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingCopayCoordinationPayment } from '~/models/dws-billing-copay-coordination-payment'
import { DwsBillingOffice } from '~/models/dws-billing-office'

/**
 * 利用者負担上限額管理結果票：明細.
 */
export type DwsBillingCopayCoordinationItem = Readonly<{
  /** 項番 */
  itemNumber: number

  /** 事業所 */
  office: DwsBillingOffice

  /** 利用者負担額集計・調整欄 */
  subtotal: DwsBillingCopayCoordinationPayment
}>
