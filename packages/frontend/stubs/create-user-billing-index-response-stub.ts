/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { createUserBillingStubs } from '~~/stubs/create-user-billing-stub'

export function createUserBillingIndexResponseStub (
  params: UserBillingsApi.GetIndexParams = {}
): UserBillingsApi.GetIndexResponse {
  return createIndexResponse(params, 20, createUserBillingStubs)
}
