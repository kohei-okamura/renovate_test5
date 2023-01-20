/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingStatementCopayCoordinationStatus } from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { range } from '@zinger/helpers'
import { DwsBillingBundle } from '~/models/dws-billing-bundle'
import { DwsBillingStatement, DwsBillingStatementId } from '~/models/dws-billing-statement'
import { DwsBillingUser } from '~/models/dws-billing-user'
import { createDwsBillingBundleStub } from '~~/stubs/create-dws-billing-bundle-stub'
import { createDwsBillingOfficeStub } from '~~/stubs/create-dws-billing-office-stub'
import { createDwsBillingStatementAggregateStubs } from '~~/stubs/create-dws-billing-statement-aggregate-stub'
import { createDwsBillingStatementContractStubs } from '~~/stubs/create-dws-billing-statement-contract-stub'
import { createDwsBillingStatementItemStubs } from '~~/stubs/create-dws-billing-statement-item-stub'
import {
  DWS_BILLING_SEEDS,
  DWS_BILLING_STATEMENT_ID_MIN,
  DWS_BILLING_STATEMENT_STUB_COUNT_PER_BUNDLE
} from '~~/stubs/create-dws-billing-stub-settings'
import { createDwsBillingUserStub } from '~~/stubs/create-dws-billing-user-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'

type CreateStubParams = {
  bundle?: DwsBillingBundle
  id?: DwsBillingStatementId
  user?: DwsBillingUser
}

export function createDwsBillingStatementStub (params: CreateStubParams = {}): DwsBillingStatement {
  const bundle = params.bundle ?? createDwsBillingBundleStub()
  const user = params.user ?? createDwsBillingUserStub()
  const id = params.id ?? DWS_BILLING_STATEMENT_ID_MIN
  const faker = createFaker(DWS_BILLING_SEEDS[id - 1])
  const status = faker.randomElement(DwsBillingStatus.values)
  const copayCoordinationStatus = faker.randomElement(DwsBillingStatementCopayCoordinationStatus.values)
  const copayCoordination = (status => {
    switch (status) {
      case DwsBillingStatementCopayCoordinationStatus.fulfilled:
        return {
          office: createDwsBillingOfficeStub(faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)),
          result: faker.randomElement(CopayCoordinationResult.values),
          amount: faker.intBetween(10000, 99999)
        }
      default:
        return undefined
    }
  })(copayCoordinationStatus)
  return {
    id,
    dwsBillingId: bundle.dwsBillingId,
    dwsBillingBundleId: bundle.id,
    subsidyCityCode: '助成自治体番号',
    user,
    dwsAreaGradeName: '地域区分名',
    dwsAreaGradeCode: '地域区分コード',
    copayLimit: faker.intBetween(1000, 30000),
    totalScore: faker.intBetween(100000, 999999),
    totalFee: faker.intBetween(100000, 999999),
    totalCappedCopay: faker.intBetween(100000, 999999),
    totalAdjustedCopay: faker.intBetween(100000, 999999),
    totalCoordinatedCopay: faker.intBetween(100000, 999999),
    totalCopay: faker.intBetween(100000, 999999),
    totalBenefit: faker.intBetween(100000, 999999),
    totalSubsidy: faker.intBetween(100000, 999999),
    isProvided: faker.randomBoolean(),
    copayCoordination,
    copayCoordinationStatus,
    aggregates: createDwsBillingStatementAggregateStubs(id),
    contracts: createDwsBillingStatementContractStubs(id),
    items: createDwsBillingStatementItemStubs(id),
    status,
    fixedAt: status === DwsBillingStatus.fixed ? faker.randomDateTimeString() : undefined,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

type CreateStubsForBundleParams = {
  bundle: DwsBillingBundle
  users: DwsBillingUser[]
}

type CreateStubsForBundle = {
  (params: CreateStubsForBundleParams): DwsBillingStatement[]
}

export const createDwsBillingStatementStubsForBundle: CreateStubsForBundle = ({ bundle, users }) => {
  const numberOfStubs = Math.min(users.length, DWS_BILLING_STATEMENT_STUB_COUNT_PER_BUNDLE)
  const max = bundle.id * numberOfStubs
  const min = max - numberOfStubs + 1
  return range(min, max).map((id, i) => {
    const user = users[i]
    return createDwsBillingStatementStub({ bundle, id, user })
  })
}
