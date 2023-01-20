/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingServiceReportAggregateCategory } from '@zinger/enums/lib/dws-billing-service-report-aggregate-category'
import { DwsBillingServiceReportAggregateGroup } from '@zinger/enums/lib/dws-billing-service-report-aggregate-group'
import { DwsBillingServiceReportFormat } from '@zinger/enums/lib/dws-billing-service-report-format'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { range } from '@zinger/helpers'
import { DwsBillingBundle } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordinationId } from '~/models/dws-billing-copay-coordination'
import { DwsBillingServiceReport } from '~/models/dws-billing-service-report'
import { DwsBillingServiceReportAggregate } from '~/models/dws-billing-service-report-aggregate'
import { DwsBillingUser } from '~/models/dws-billing-user'
import { createDwsBillingServiceReportItemStubs } from '~~/stubs/create-dws-billing-service-report-item-stub'
import {
  DWS_BILLING_SEEDS,
  DWS_BILLING_STATEMENT_STUB_COUNT_PER_BUNDLE
} from '~~/stubs/create-dws-billing-stub-settings'
import { createFaker, Faker } from '~~/stubs/fake'

const createAggregate = (faker: Faker): DwsBillingServiceReportAggregate => {
  const groupEntries = DwsBillingServiceReportAggregateGroup.values.map(group => {
    const categoryEntries = DwsBillingServiceReportAggregateCategory.values
      .filter(x => x !== DwsBillingServiceReportAggregateCategory.categoryTotal)
      .map(category => [category, faker.intBetween(10000, 7200000)])
    return [group, Object.fromEntries(categoryEntries)]
  })
  return Object.fromEntries(groupEntries)
}

type CreateStubParams = {
  bundle: DwsBillingBundle
  user: DwsBillingUser
  id: DwsBillingCopayCoordinationId
  numberOfItems?: number
}

export const createDwsBillingServiceReportStub = (params: CreateStubParams): DwsBillingServiceReport => {
  const { bundle, user, id, numberOfItems } = params
  const faker = createFaker(DWS_BILLING_SEEDS[id - 1])
  const status = faker.randomElement(DwsBillingStatus.values)
  return {
    id,
    dwsBillingId: bundle.dwsBillingId,
    dwsBillingBundleId: bundle.id,
    user,
    format: faker.randomElement(DwsBillingServiceReportFormat.values),
    plan: createAggregate(faker),
    result: createAggregate(faker),
    emergencyCount: faker.intBetween(10, 99),
    firstTimeCount: faker.intBetween(10, 99),
    welfareSpecialistCooperationCount: faker.intBetween(10, 99),
    behavioralDisorderSupportCooperationCount: faker.intBetween(10, 99),
    movingCareSupportCount: faker.intBetween(10, 99),
    items: createDwsBillingServiceReportItemStubs({ bundle, id, numberOfItems }),
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
  (params: CreateStubsForBundleParams): DwsBillingServiceReport[]
}

export const createDwsBillingServiceReportStubsForBundle: CreateStubsForBundle = ({ bundle, users }) => {
  const numberOfStubs = Math.min(users.length, DWS_BILLING_STATEMENT_STUB_COUNT_PER_BUNDLE)
  const numberOfItems = users.length === 1 ? 1 : undefined
  const max = bundle.id * numberOfStubs
  const min = max - numberOfStubs + 1
  return range(min, max).map((id, i) => {
    const user = users[i]
    return createDwsBillingServiceReportStub({ bundle, id, user, numberOfItems })
  })
}
