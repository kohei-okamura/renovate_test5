/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 利用者負担上限額管理結果票：費用.
 */
export type DwsBillingCopayCoordinationPayment = Readonly<{
  /** 総費用額 */
  fee: number

  /** 利用者負担額 */
  copay: number

  /** 管理結果後利用者負担額 */
  coordinatedCopay: number
}>
