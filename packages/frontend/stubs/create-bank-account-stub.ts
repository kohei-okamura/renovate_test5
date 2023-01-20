/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'
import { BankAccount, BankAccountId } from '~/models/bank-account'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs'
import { createFaker } from '~~/stubs/fake'
import { banks, blanches } from '~~/stubs/fake/banks'

export const BANK_ID_MAX = ID_MAX
export const BANK_ID_MIN = ID_MIN

export function createBankAccountStub (id: BankAccountId = BANK_ID_MIN): BankAccount {
  const faker = createFaker(SEEDS[id - 1])
  const fake = faker.createFake()
  const bank = faker.randomElement(banks)
  const blanch = faker.randomElement(blanches[bank.code])
  return {
    id,
    bankName: bank.name,
    bankCode: bank.code,
    bankBranchName: blanch.name,
    bankBranchCode: blanch.code,
    bankAccountType: faker.randomElement([
      BankAccountType.ordinaryDeposit,
      BankAccountType.currentDeposit,
      BankAccountType.fixedDeposit
    ]),
    bankAccountNumber: faker.randomNumericString(7),
    bankAccountHolder: fake.name.phoneticDisplayName,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createEmptyBankAccountStub (id: BankAccountId = BANK_ID_MIN): BankAccount {
  return {
    id,
    bankName: '',
    bankCode: '',
    bankBranchName: '',
    bankBranchCode: '',
    bankAccountType: BankAccountType.ordinaryDeposit,
    bankAccountNumber: '',
    bankAccountHolder: '',
    createdAt: '',
    updatedAt: ''
  }
}
