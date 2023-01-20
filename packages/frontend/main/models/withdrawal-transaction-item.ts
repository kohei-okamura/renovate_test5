/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserBillingId } from '~/models/user-billing'
import { ZenginDataRecord } from '~/models/zengin-data-record'

/**
 * 口座振替データ：明細.
 */
export type WithdrawalTransactionItem = Readonly<{
  /** 利用者請求 ID */
  userBillingIds: UserBillingId[]

  /** 全銀データ */
  zenginRecord: ZenginDataRecord
}>
