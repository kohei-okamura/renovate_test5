/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBilling } from '~/models/dws-billing'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { createDwsBillingStubs } from '~~/stubs/create-dws-billing-stub'
import { DWS_BILLING_STUB_COUNT } from '~~/stubs/create-dws-billing-stub-settings'
import { createIndexResponse } from '~~/stubs/create-index-response'

export function createDwsBillingIndexResponseStub (
  params: DwsBillingsApi.GetIndexParams = {},
  filter?: (v: DwsBilling) => boolean,
  count = DWS_BILLING_STUB_COUNT
): DwsBillingsApi.GetIndexResponse {
  return createIndexResponse(params, count, createDwsBillingStubs, filter)
}
