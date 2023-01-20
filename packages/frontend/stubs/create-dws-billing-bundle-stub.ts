/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { range } from '@zinger/helpers/index'
import { DwsBilling } from '~/models/dws-billing'
import { DwsBillingBundle, DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { createDwsBillingStub } from '~~/stubs/create-dws-billing-stub'
import { DWS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING } from '~~/stubs/create-dws-billing-stub-settings'
import { createFaker } from '~~/stubs/fake'
import { SEEDS } from '~~/stubs/index'

type CreateStubParams = {
  billing?: DwsBilling
  id?: DwsBillingBundleId
  cityName?: DwsBillingBundle['cityName']
  providedIn?: DwsBillingBundle['providedIn']
}

export const createDwsBillingBundleStub = (params: CreateStubParams = {}): DwsBillingBundle => {
  const { providedIn, cityName } = params
  const billing = params.billing ?? createDwsBillingStub()
  const id = params.id ?? (billing.id - 1) * DWS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING + 1
  const faker = createFaker(SEEDS[id - 1])
  return {
    id,
    dwsBillingId: billing.id,
    providedIn: providedIn ?? faker.randomYearMonthString(),
    cityCode: faker.randomNumericString(5),
    cityName: cityName ?? faker.createFake().addr.city,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createDwsBillingBundleStubsForBilling = (billing: DwsBilling): DwsBillingBundle[] => {
  const max = billing.id * DWS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING
  const min = max - DWS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING + 1
  const faker = createFaker(SEEDS[min - 1])
  const fixedMonth = faker.randomYearMonthString()
  const fixedCity = faker.createFake().addr.city
  return range(min, max).map(id => createDwsBillingBundleStub({
    id,
    billing,
    providedIn: id % 3 === 1 ? fixedMonth : undefined,
    cityName: id % 4 === 1 ? fixedCity : undefined
  }))
}
