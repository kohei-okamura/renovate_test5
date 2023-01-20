/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { range } from '@zinger/helpers'
import { DwsBillingCopayCoordinationItem } from '~/models/dws-billing-copay-coordination-item'
import { createDwsBillingCopayCoordinationPaymentStub } from '~~/stubs/create-dws-billing-copay-coordination-payment-stub'
import { createDwsBillingOfficeStub } from '~~/stubs/create-dws-billing-office-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, ID_MIN, SEEDS } from '~~/stubs/index'

export const DWS_BILLING_COPAY_COORDINATION_ITEM_ITEM_NUMBER_MIN = ID_MIN
export const DWS_BILLING_COPAY_COORDINATION_ITEM_ITEM_STUB_COUNT = 10

export function createDwsBillingCopayCoordinationItemStub (
  itemNumber = DWS_BILLING_COPAY_COORDINATION_ITEM_ITEM_NUMBER_MIN
): DwsBillingCopayCoordinationItem {
  const faker = createFaker(SEEDS[itemNumber - 1])
  return {
    itemNumber,
    office: createDwsBillingOfficeStub(faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)),
    subtotal: createDwsBillingCopayCoordinationPaymentStub(itemNumber)
  }
}

export const createDwsBillingCopayCoordinationItemStubs: CreateStubs<DwsBillingCopayCoordinationItem> = (
  n = DWS_BILLING_COPAY_COORDINATION_ITEM_ITEM_STUB_COUNT,
  skip = 0
) => {
  const start = DWS_BILLING_COPAY_COORDINATION_ITEM_ITEM_NUMBER_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createDwsBillingCopayCoordinationItemStub)
}
