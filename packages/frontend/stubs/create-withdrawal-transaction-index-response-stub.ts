/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { WithdrawalTransactionsApi } from '~/services/api/withdrawal-transactions-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { createWithdrawalTransactionStubs } from '~~/stubs/create-withdrawal-transaction-stub'

export function createWithdrawalTransactionIndexResponseStub (
  params: WithdrawalTransactionsApi.GetIndexParams = {}
): WithdrawalTransactionsApi.GetIndexResponse {
  return createIndexResponse(params, 20, createWithdrawalTransactionStubs)
}
