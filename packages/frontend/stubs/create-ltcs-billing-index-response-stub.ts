/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBilling } from '~/models/ltcs-billing'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { createLtcsBillingStubs } from '~~/stubs/create-ltcs-billing-stub'
import { LTCS_BILLING_STUB_COUNT } from '~~/stubs/create-ltcs-billing-stub-settings'

export const createLtcsBillingIndexResponseStub = (
  params: LtcsBillingsApi.GetIndexParams = {},
  filter?: (v: LtcsBilling) => boolean
): LtcsBillingsApi.GetIndexResponse => {
  return createIndexResponse(params, LTCS_BILLING_STUB_COUNT, createLtcsBillingStubs, filter)
}
