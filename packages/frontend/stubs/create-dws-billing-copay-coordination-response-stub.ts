/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordinationId } from '~/models/dws-billing-copay-coordination'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import { createDwsBillingBundleStub } from '~~/stubs/create-dws-billing-bundle-stub'
import {
  createDwsBillingCopayCoordinationStub,
  DWS_BILLING_COPAY_COORDINATION_ID_MIN
} from '~~/stubs/create-dws-billing-copay-coordination-stub'
import { createDwsBillingStub } from '~~/stubs/create-dws-billing-stub'
import {
  DWS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING,
  DWS_BILLING_ID_MIN
} from '~~/stubs/create-dws-billing-stub-settings'
import { createDwsBillingUserStub } from '~~/stubs/create-dws-billing-user-stub'

type CreateDwsBillingCopayCoordinationResponseStubParams = {
  id?: DwsBillingCopayCoordinationId
  billingId?: DwsBillingId
  bundleId?: DwsBillingBundleId
}

export function createDwsBillingCopayCoordinationResponseStub (
  params: CreateDwsBillingCopayCoordinationResponseStubParams = {}
): DwsBillingCopayCoordinationsApi.GetResponse {
  const id = params.id ?? DWS_BILLING_COPAY_COORDINATION_ID_MIN
  const billingId = params.billingId ?? DWS_BILLING_ID_MIN
  const bundleId = params.bundleId ?? billingId * DWS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING

  const billing = createDwsBillingStub({ id: billingId })
  const bundle = createDwsBillingBundleStub({ billing, id: bundleId })
  const user = createDwsBillingUserStub()
  const copayCoordination = createDwsBillingCopayCoordinationStub({ bundleId, id, user })
  return {
    billing,
    bundle,
    copayCoordination
  }
}
