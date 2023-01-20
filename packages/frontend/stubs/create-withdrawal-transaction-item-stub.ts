/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { range } from '@zinger/helpers'
import { WithdrawalTransactionItem } from '~/models/withdrawal-transaction-item'
import { createUserBillingStubs } from '~~/stubs/create-user-billing-stub'
import { createZenginDataRecordStub } from '~~/stubs/create-zengin-data-record-stub'
import { CreateStubs, ID_MIN } from '~~/stubs/index'

export const WITHDRAWAL_TRANSACTION_ITEM_ID_MIN = ID_MIN
export const WITHDRAWAL_TRANSACTION_ITEM_STUB_COUNT = 10

export function createWithdrawalTransactionItemStub (salt: number): WithdrawalTransactionItem {
  const userBillingIds = createUserBillingStubs(5, salt).map(x => x.id)
  const zenginRecord = createZenginDataRecordStub(salt)
  return {
    userBillingIds,
    zenginRecord
  }
}

export const createWithdrawalTransactionItemStubs: CreateStubs<WithdrawalTransactionItem> = (
  n = WITHDRAWAL_TRANSACTION_ITEM_STUB_COUNT,
  skip = 0
) => {
  const start = WITHDRAWAL_TRANSACTION_ITEM_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createWithdrawalTransactionItemStub)
}
