/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingCopayCoordinationExchangeAim } from '@zinger/enums/lib/dws-billing-copay-coordination-exchange-aim'
import { DwsBillingStatementCopayCoordinationStatus } from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordination, DwsBillingCopayCoordinationId } from '~/models/dws-billing-copay-coordination'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { DwsBillingUser } from '~/models/dws-billing-user'
import { createDwsBillingCopayCoordinationItemStubs } from '~~/stubs/create-dws-billing-copay-coordination-item-stub'
import { createDwsBillingCopayCoordinationPaymentStub } from '~~/stubs/create-dws-billing-copay-coordination-payment-stub'
import { createDwsBillingOfficeStub } from '~~/stubs/create-dws-billing-office-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

type Params = {
  id?: DwsBillingCopayCoordinationId
  bundleId: DwsBillingBundleId
  user: DwsBillingUser
}

type CreateStubs<T> = {
  (param: { statements: DwsBillingStatement[] }, skip?: number): T[]
}

export const DWS_BILLING_COPAY_COORDINATION_ID_MAX = ID_MAX
export const DWS_BILLING_COPAY_COORDINATION_ID_MIN = ID_MIN

export function createDwsBillingCopayCoordinationStub (params: Params): DwsBillingCopayCoordination {
  const id = params.id ?? DWS_BILLING_COPAY_COORDINATION_ID_MIN
  const { bundleId, user } = params

  const faker = createFaker(SEEDS[id - 1])
  const items = createDwsBillingCopayCoordinationItemStubs()
  return {
    id,
    dwsBillingId: 1,
    dwsBillingBundleId: bundleId,
    office: createDwsBillingOfficeStub(faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)),
    user,
    items,
    result: faker.randomElement(CopayCoordinationResult.values),
    exchangeAim: DwsBillingCopayCoordinationExchangeAim.declaration,
    total: createDwsBillingCopayCoordinationPaymentStub(id, items),
    status: faker.randomElement(DwsBillingStatus.values),
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createDwsBillingCopayCoordinationStubs: CreateStubs<DwsBillingCopayCoordination> = (
  { statements }
) => {
  return statements
    .map(statement => {
      const { copayCoordinationStatus, dwsBillingBundleId: bundleId, id, user } = statement
      const statuses: DwsBillingStatementCopayCoordinationStatus[] = [
        DwsBillingStatementCopayCoordinationStatus.unapplicable,
        DwsBillingStatementCopayCoordinationStatus.uncreated
      ]
      return statuses.includes(copayCoordinationStatus)
        ? undefined
        : createDwsBillingCopayCoordinationStub({
          id,
          bundleId,
          user
        })
    })
    .filter((v): v is DwsBillingCopayCoordination => v !== undefined)
}
