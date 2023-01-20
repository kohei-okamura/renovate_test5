/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingCopayCoordinationExchangeAim } from '@zinger/enums/lib/dws-billing-copay-coordination-exchange-aim'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { DateLike } from '~/models/date'
import { DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordinationItem } from '~/models/dws-billing-copay-coordination-item'
import { DwsBillingCopayCoordinationPayment } from '~/models/dws-billing-copay-coordination-payment'
import { DwsBillingOffice } from '~/models/dws-billing-office'
import { DwsBillingUser } from '~/models/dws-billing-user'

/**
 * 利用者負担上限額管理結果票 ID.
 */
export type DwsBillingCopayCoordinationId = number

/**
 * 利用者負担上限額管理結果票.
 */
export type DwsBillingCopayCoordination = Readonly<{
  /** 利用者負担上限額管理結果票 ID */
  id: DwsBillingCopayCoordinationId

  /** 請求ID */
  dwsBillingId: DwsBillingId

  /** 請求単位 ID */
  dwsBillingBundleId: DwsBillingBundleId

  /** 上限管理事業所 */
  office: DwsBillingOffice

  /** 上限管理対象利用者 */
  user: DwsBillingUser

  /** 明細 */
  items: DwsBillingCopayCoordinationItem[]

  /** 利用者負担上限額管理結果 */
  result: CopayCoordinationResult

  /** 合計 */
  total: DwsBillingCopayCoordinationPayment

  /** 作成区分 */
  exchangeAim: DwsBillingCopayCoordinationExchangeAim

  /** 状態 */
  status: DwsBillingStatus

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
