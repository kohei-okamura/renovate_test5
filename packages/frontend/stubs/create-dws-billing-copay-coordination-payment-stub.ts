/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingCopayCoordinationItem } from '~/models/dws-billing-copay-coordination-item'
import { DwsBillingCopayCoordinationPayment } from '~/models/dws-billing-copay-coordination-payment'
import { DWS_BILLING_COPAY_COORDINATION_ID_MIN } from '~~/stubs/create-dws-billing-copay-coordination-stub'
import { createFaker } from '~~/stubs/fake'
import { SEEDS } from '~~/stubs/index'

export function createDwsBillingCopayCoordinationPaymentStub (
  id = DWS_BILLING_COPAY_COORDINATION_ID_MIN,
  items?: DwsBillingCopayCoordinationItem[]
): DwsBillingCopayCoordinationPayment {
  const faker = createFaker(SEEDS[id - 1])
  return items
    ? items.reduce((acc, cur) => ({
      fee: acc.fee + cur.subtotal.fee,
      copay: acc.copay + cur.subtotal.copay,
      coordinatedCopay: acc.coordinatedCopay + cur.subtotal.coordinatedCopay
    }), { fee: 0, copay: 0, coordinatedCopay: 0 })
    : {
      fee: faker.intBetween(1000, 99999),
      copay: faker.intBetween(1000, 99999),
      coordinatedCopay: faker.intBetween(1000, 99999)
    }
}
