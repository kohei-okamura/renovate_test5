/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { range } from '@zinger/helpers'
import { DwsBillingStatementId } from '~/models/dws-billing-statement'
import { DwsBillingStatementItem } from '~/models/dws-billing-statement-item'
import { getServiceCodeDictionary } from '~~/stubs/create-dws-billing-statement-response-stub'
import { DWS_BILLING_STATEMENT_ID_MIN } from '~~/stubs/create-dws-billing-stub-settings'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, SEEDS } from '~~/stubs/index'

export const DWS_BILLING_STATEMENT_STUB_COUNT = 12

export function createDwsBillingStatementItemStub (
  id: DwsBillingStatementId = DWS_BILLING_STATEMENT_ID_MIN
): DwsBillingStatementItem {
  const serviceCodes = Object.keys(getServiceCodeDictionary())
  const last = serviceCodes.length - 1
  const faker = createFaker(SEEDS[id - 1])
  return {
    serviceCode: serviceCodes[faker.intBetween(0, last)],
    serviceCodeCategory: faker.randomElement(DwsServiceCodeCategory.values),
    unitScore: faker.intBetween(1000, 9999),
    count: faker.intBetween(1, 99),
    totalScore: faker.intBetween(10000, 99999)
  }
}

export const createDwsBillingStatementItemStubs: CreateStubs<DwsBillingStatementItem> = (
  id: DwsBillingStatementId = DWS_BILLING_STATEMENT_ID_MIN,
  n = DWS_BILLING_STATEMENT_STUB_COUNT,
  skip = 0
) => {
  const start = id + skip
  const end = start + n - 1
  return range(start, end).map(createDwsBillingStatementItemStub)
}
