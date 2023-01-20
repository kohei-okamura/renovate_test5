/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { range } from '@zinger/helpers'
import { DwsBillingStatementId } from '~/models/dws-billing-statement'
import { DwsBillingStatementAggregate } from '~/models/dws-billing-statement-aggregate'
import { DWS_BILLING_STATEMENT_ID_MIN } from '~~/stubs/create-dws-billing-stub-settings'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, SEEDS } from '~~/stubs/index'

export const createDwsBillingStatementAggregateStub = (
  id: DwsBillingStatementId = DWS_BILLING_STATEMENT_ID_MIN
): DwsBillingStatementAggregate => {
  const faker = createFaker(SEEDS[id - 1])
  return {
    serviceDivisionCode: faker.randomElement(DwsServiceDivisionCode.values),
    startedOn: faker.randomDateTimeString(),
    terminatedOn: faker.randomDateTimeString(),
    serviceDays: faker.intBetween(1, 99),
    subtotalScore: faker.intBetween(100000, 200000),
    unitCost: faker.intBetween(10, 9999),
    subtotalFee: faker.intBetween(100000, 200000),
    unmanagedCopay: faker.intBetween(10000, 20000),
    managedCopay: faker.intBetween(10000, 20000),
    cappedCopay: faker.intBetween(100000, 200000),
    adjustedCopay: faker.intBetween(100000, 200000),
    coordinatedCopay: faker.intBetween(100000, 200000),
    subtotalCopay: faker.intBetween(100000, 200000),
    subtotalBenefit: faker.intBetween(100000, 200000),
    subtotalSubsidy: faker.intBetween(100000, 200000)
  }
}

export const createDwsBillingStatementAggregateStubs: CreateStubs<DwsBillingStatementAggregate> = (
  id: DwsBillingStatementId = DWS_BILLING_STATEMENT_ID_MIN,
  skip = 0
) => {
  const start = id + skip
  const end = start + DwsServiceDivisionCode.size - 1
  return range(start, end).map(n => createDwsBillingStatementAggregateStub(id + n))
}
