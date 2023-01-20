/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DefrayerCategory } from '@zinger/enums/lib/defrayer-category'
import { range } from '@zinger/helpers'
import { LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundle, LtcsBillingBundleId } from '~/models/ltcs-billing-bundle'
import { LtcsBillingInvoice, LtcsBillingInvoiceId } from '~/models/ltcs-billing-invoice'
import {
  LTCS_BILLING_INVOICE_STUB_COUNT_PER_BUNDLE,
  LTCS_BILLING_SEEDS,
  LTCS_BILLING_STATEMENT_STUB_COUNT_PER_INVOICE
} from '~~/stubs/create-ltcs-billing-stub-settings'
import { createFaker } from '~~/stubs/fake'

type CreateStubParams = {
  billingId: LtcsBillingId
  bundleId: LtcsBillingBundleId
  id: LtcsBillingInvoiceId
}
export const createLtcsBillingInvoiceStub = ({ billingId, bundleId, id }: CreateStubParams): LtcsBillingInvoice => {
  const faker = createFaker(LTCS_BILLING_SEEDS[id - 1])
  const isSubsidy = id % 2 === 0
  const defrayerCategory = isSubsidy ? faker.randomElement(DefrayerCategory.values) : undefined
  const totalScore = faker.intBetween(100000, 999999)
  const totalFee = Math.ceil(totalScore * 11.4)
  const copayAmount = Math.ceil(totalFee * 0.1)
  return {
    id,
    billingId,
    bundleId,
    isSubsidy,
    defrayerCategory,
    statementCount: LTCS_BILLING_STATEMENT_STUB_COUNT_PER_INVOICE,
    totalScore,
    totalFee,
    insuranceAmount: totalFee - copayAmount,
    subsidyAmount: isSubsidy ? copayAmount : 0,
    copayAmount: isSubsidy ? 0 : copayAmount,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createLtcsBillingInvoiceStubsForBundle = (
  { billingId, id: bundleId }: LtcsBillingBundle
): LtcsBillingInvoice[] => {
  const max = bundleId * LTCS_BILLING_INVOICE_STUB_COUNT_PER_BUNDLE
  const min = max - LTCS_BILLING_INVOICE_STUB_COUNT_PER_BUNDLE + 1
  return range(min, max).map(id => createLtcsBillingInvoiceStub({ billingId, bundleId, id }))
}
