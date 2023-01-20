/*
* Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
* UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
*/
import { ConsumptionTaxRate } from '@zinger/enums/lib/consumption-tax-rate'
import { UserBillingOtherItem } from '~/models/user-billing-other-item'
import { createFaker } from '~~/stubs/fake'
import { STUB_DEFAULT_SEED } from '~~/stubs/index'

export function createUserBillingOtherItemStub (): UserBillingOtherItem {
  const faker = createFaker(STUB_DEFAULT_SEED)
  const tax = faker.randomElement(ConsumptionTaxRate.values)
  const copayWithoutTax = faker.intBetween(1000, 99999)
  return {
    score: faker.intBetween(1, 999),
    unitCost: faker.intBetween(1000, 99999),
    subtotalCost: faker.intBetween(1000, 99999),
    tax,
    medicalDeductionAmount: faker.intBetween(1000, 99999),
    totalAmount: faker.intBetween(1000, 99999),
    copayWithoutTax,
    copayWithTax: Math.floor(copayWithoutTax * ((100 + tax) / 100))
  }
}
