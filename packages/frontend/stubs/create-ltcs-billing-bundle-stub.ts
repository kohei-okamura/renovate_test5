/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { range } from '@zinger/helpers'
import { LtcsBilling, LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundle, LtcsBillingBundleId } from '~/models/ltcs-billing-bundle'
import {
  LTCS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING,
  LTCS_BILLING_SEEDS
} from '~~/stubs/create-ltcs-billing-stub-settings'
import { createFaker } from '~~/stubs/fake'

type CreateStubParams = {
  billingId: LtcsBillingId
  id: LtcsBillingBundleId
}

export const createLtcsBillingBundleStub = ({ billingId, id }: CreateStubParams): LtcsBillingBundle => {
  const faker = createFaker(LTCS_BILLING_SEEDS[id - 1])
  return {
    id,
    billingId,
    providedIn: faker.randomDateString(),
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createLtcsBillingBundleStubsForBilling = ({ id: billingId }: LtcsBilling): LtcsBillingBundle[] => {
  const max = billingId * LTCS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING
  const min = max - LTCS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING + 1
  return range(min, max).map(id => createLtcsBillingBundleStub({ billingId, id }))
}
