/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { WithdrawalResultCode } from '@zinger/enums/lib/withdrawal-result-code'
import { ZenginDataRecordCode } from '@zinger/enums/lib/zengin-data-record-code'
import { ZenginDataRecord } from '~/models/zengin-data-record'
import { createBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { createFaker } from '~~/stubs/fake'
import { STUB_DEFAULT_SEED } from '~~/stubs/index'

export function createZenginDataRecordStub (salt: number): ZenginDataRecord {
  const seed = STUB_DEFAULT_SEED + `${salt}`.padStart(6, '0')
  const faker = createFaker(seed)
  const bankAccount = createBankAccountStub()
  return {
    bankCode: bankAccount.bankCode,
    bankBranchCode: bankAccount.bankBranchCode,
    bankAccountType: bankAccount.bankAccountType,
    bankAccountNumber: bankAccount.bankAccountNumber,
    bankAccountHolder: bankAccount.bankAccountHolder,
    amount: faker.intBetween(10000, 100000),
    dataRecordCode: faker.randomElement(ZenginDataRecordCode.values),
    clientNumber: faker.randomNumericString(10),
    withdrawalResultCode: faker.randomElement(WithdrawalResultCode.values)
  }
}
