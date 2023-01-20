/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingOffice } from '~/models/dws-billing-office'

/**
 * 障害福祉サービス：明細書：上限管理結果.
 */
export type DwsBillingStatementCopayCoordination = Readonly<{
  /** 事業所 */
  office: DwsBillingOffice

  /** 管理結果 */
  result: CopayCoordinationResult

  /** 管理結果額 */
  amount: number
}>
