/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { LtcsCalcType } from '@zinger/enums/lib/ltcs-calc-type'
import { LtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { LtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'
import { LtcsServiceDivisionCode } from '@zinger/enums/lib/ltcs-service-division-code'
import { range } from '@zinger/helpers'
import { LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundleId } from '~/models/ltcs-billing-bundle'
import { LtcsBillingInvoice } from '~/models/ltcs-billing-invoice'
import { LtcsBillingStatement, LtcsBillingStatementId } from '~/models/ltcs-billing-statement'
import { LtcsBillingStatementItem } from '~/models/ltcs-billing-statement-item'
import {
  LTCS_BILLING_SEEDS,
  LTCS_BILLING_STATEMENT_STUB_COUNT_PER_INVOICE
} from '~~/stubs/create-ltcs-billing-stub-settings'
import { createLtcsBillingUserStub } from '~~/stubs/create-ltcs-billing-user-stub'
import { createLtcsHomeVisitLongTermCareDictionaryStubs } from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-stub'
import { createOfficeStub, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker, Faker } from '~~/stubs/fake'

const entries = createLtcsHomeVisitLongTermCareDictionaryStubs().filter(x => x.score.calcType === LtcsCalcType.score)

const createItemStub = (faker: Faker, hasSubsidy: boolean): LtcsBillingStatementItem => {
  const count = faker.intBetween(1, 20)
  const entry = faker.randomElement(entries)
  const unitScore = entry.score.value
  const totalScore = unitScore * count
  return {
    serviceCode: entry.serviceCode,
    serviceCodeCategory: faker.randomElement(LtcsServiceCodeCategory.values),
    unitScore,
    count,
    totalScore,
    subsidies: hasSubsidy ? [{ count, totalScore }] : [],
    note: ''
  }
}

type CreateStubParams = {
  billingId: LtcsBillingId
  bundleId: LtcsBillingBundleId
  id: LtcsBillingStatementId
}
export const createLtcsBillingStatementStub = ({ billingId, bundleId, id }: CreateStubParams): LtcsBillingStatement => {
  const faker = createFaker(LTCS_BILLING_SEEDS[id - 1])
  const fake = faker.createFake()
  const userId = (id % LTCS_BILLING_STATEMENT_STUB_COUNT_PER_INVOICE) + 1
  const hasSubsidy = faker.randomBoolean()
  const items = range(1, faker.intBetween(5, 20)).map(() => createItemStub(faker, hasSubsidy))
  const authorType = faker.randomElement([
    LtcsCarePlanAuthorType.careManagerOffice,
    LtcsCarePlanAuthorType.self
  ])
  // createOfficeStub では先頭の4件が必ず事業所番号を持っているので先頭4件に絞って決定する
  const officeId = authorType === LtcsCarePlanAuthorType.careManagerOffice
    ? faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MIN + 3)
    : undefined
  const office = officeId ? createOfficeStub(officeId) : undefined
  const status = faker.randomElement(LtcsBillingStatus.values.filter(x => x !== LtcsBillingStatus.disabled))
  const benefitRate = faker.randomElement([90, 80, 70])
  const totalScore = items.reduce((z, x) => z + x.totalScore, 0)
  const totalAmount = Math.floor(totalScore * 11.4)
  const claimAmount = Math.floor(totalAmount * benefitRate / 100)
  const copayAmount = totalAmount - claimAmount
  return {
    id,
    billingId,
    bundleId,
    insurerNumber: faker.randomNumericString(6),
    insurerName: fake.addr.city,
    user: createLtcsBillingUserStub(userId),
    carePlanAuthor: {
      authorType,
      officeId,
      code: office?.ltcsHomeVisitLongTermCareService?.code ?? '',
      name: office?.name ?? ''
    },
    agreedOn: faker.randomDateString(),
    expiredOn: faker.randomDateString(),
    expiredReason: LtcsExpiredReason.hospitalized,
    insurance: {
      benefitRate,
      totalScore,
      claimAmount,
      copayAmount
    },
    subsidies: hasSubsidy
      ? [{
        defrayerNumber: faker.randomNumericString(7),
        recipientNumber: faker.randomNumericString(8),
        benefitRate: 100,
        totalScore,
        claimAmount: copayAmount,
        copayAmount: 0
      }]
      : [],
    items,
    aggregates: [{
      serviceDivisionCode: LtcsServiceDivisionCode.homeVisitLongTermCare,
      serviceDays: Math.max(...items.map(x => x.count)),
      plannedScore: faker.intBetween(100000, 999999),
      managedScore: faker.intBetween(100000, 999999),
      unmanagedScore: faker.intBetween(10000, 99999),
      insurance: {
        totalScore,
        unitCost: 114000,
        claimAmount,
        copayAmount
      },
      subsidies: hasSubsidy
        ? [{
          totalScore,
          claimAmount: copayAmount,
          copayAmount: 0
        }]
        : []
    }],
    status,
    fixedAt: status === LtcsBillingStatus.fixed ? faker.randomDateTimeString() : undefined,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createLtcsBillingStatementStubsForInvoice = (
  { billingId, bundleId, id: invoiceId }: LtcsBillingInvoice
): LtcsBillingStatement[] => {
  const max = invoiceId * LTCS_BILLING_STATEMENT_STUB_COUNT_PER_INVOICE
  const min = max - LTCS_BILLING_STATEMENT_STUB_COUNT_PER_INVOICE + 1
  return range(min, max).map(id => createLtcsBillingStatementStub({ billingId, bundleId, id }))
}
