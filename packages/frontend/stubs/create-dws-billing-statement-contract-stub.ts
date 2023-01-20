/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsGrantedServiceCode } from '@zinger/enums/lib/dws-granted-service-code'
import { range } from '@zinger/helpers'
import { DwsBillingStatementId } from '~/models/dws-billing-statement'
import { DwsBillingStatementContract } from '~/models/dws-billing-statement-contract'
import { DWS_BILLING_STATEMENT_ID_MIN } from '~~/stubs/create-dws-billing-stub-settings'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, SEEDS } from '~~/stubs/index'

export const DWS_BILLING_STATEMENT_CONTRACT_STUB_COUNT = 3

export const createDwsBillingStatementContractStub = (
  id: DwsBillingStatementId = DWS_BILLING_STATEMENT_ID_MIN
): DwsBillingStatementContract => {
  const faker = createFaker(SEEDS[id - 1])
  return {
    dwsGrantedServiceCode: faker.randomElement(DwsGrantedServiceCode.values),
    grantedAmount: 0,
    agreedOn: faker.randomDateTimeString(),
    expiredOn: faker.randomDateTimeString(),
    indexNumber: 0
  }
}

export const createDwsBillingStatementContractStubs: CreateStubs<DwsBillingStatementContract> = (
  id: DwsBillingStatementId = DWS_BILLING_STATEMENT_ID_MIN,
  n = DWS_BILLING_STATEMENT_CONTRACT_STUB_COUNT,
  skip = 0
) => {
  const start = id + skip
  const end = start + n - 1
  return range(start, end).map(() => createDwsBillingStatementContractStub(id))
}
