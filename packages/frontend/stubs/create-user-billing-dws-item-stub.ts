/*
* Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
* UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
*/
import { ConsumptionTaxRate } from '@zinger/enums/lib/consumption-tax-rate'
import { DwsBillingStatementId } from '~/models/dws-billing-statement'
import { UserBillingDwsItem } from '~/models/user-billing-dws-item'
import { createFaker } from '~~/stubs/fake'
import { STUB_DEFAULT_SEED } from '~~/stubs/index'

export function createUserBillingDwsItemStub (id: DwsBillingStatementId): UserBillingDwsItem {
  const seed = STUB_DEFAULT_SEED + `${id}`.padStart(6, '0')
  const faker = createFaker(seed)
  const tax = faker.randomElement(ConsumptionTaxRate.values)
  const copayWithoutTax = faker.intBetween(1000, 99999)
  return {
    dwsStatementId: id,
    score: faker.intBetween(10, 999),
    unitCost: faker.intBetween(1000, 99999),
    subtotalCost: faker.intBetween(1000, 99999),
    tax,
    medicalDeductionAmount: faker.intBetween(1000, 99999),
    benefitAmount: faker.intBetween(1000, 99999),
    subsidyAmount: faker.intBetween(1000, 99999),
    totalAmount: faker.intBetween(1000, 99999),
    copayWithoutTax,
    copayWithTax: Math.floor(copayWithoutTax * ((100 + tax) / 100))
  }
}
