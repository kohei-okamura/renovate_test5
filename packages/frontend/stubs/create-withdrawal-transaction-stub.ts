/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { range } from '@zinger/helpers'
import { WithdrawalTransaction, WithdrawalTransactionId } from '~/models/withdrawal-transaction'
import { createWithdrawalTransactionItemStubs } from '~~/stubs/create-withdrawal-transaction-item-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, ID_MAX, ID_MIN, STUB_COUNT, STUB_DEFAULT_SEED } from '~~/stubs/index'

export const WITHDRAWAL_TRANSACTION_ID_MAX = ID_MAX
export const WITHDRAWAL_TRANSACTION_ID_MIN = ID_MIN
export const WITHDRAWAL_TRANSACTION_STUB_COUNT = STUB_COUNT

export function createWithdrawalTransactionStub (
  id: WithdrawalTransactionId = WITHDRAWAL_TRANSACTION_ID_MIN
): WithdrawalTransaction {
  const seed = STUB_DEFAULT_SEED + `${id}`.padStart(6, '0')
  const faker = createFaker(seed)
  const dateStr = faker.randomDateTimeString()
  const downloadedAt = faker.randomElement(
    [
      undefined,
      faker.randomDateTime().plus({ days: faker.intBetween(1, 100) }).toISODate()
    ]
  )
  return {
    id,
    items: createWithdrawalTransactionItemStubs(faker.intBetween(1, 100)),
    deductedOn: faker.randomDateTimeString(),
    downloadedAt,
    createdAt: dateStr,
    updatedAt: dateStr
  }
}

export const createWithdrawalTransactionStubs: CreateStubs<WithdrawalTransaction> = (
  n = WITHDRAWAL_TRANSACTION_STUB_COUNT,
  skip = 0
) => {
  const start = WITHDRAWAL_TRANSACTION_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createWithdrawalTransactionStub)
}
