/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserBillingId } from '~/models/user-billing'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { createUserBillingStub } from '~~/stubs/create-user-billing-stub'

export const createUserBillingResponseStub = (id: UserBillingId): UserBillingsApi.GetResponse => ({
  userBilling: createUserBillingStub(id)
})
