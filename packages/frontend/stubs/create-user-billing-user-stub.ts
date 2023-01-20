/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserId } from '~/models/user'
import { UserBillingUser } from '~/models/user-billing-user'
import { createBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { createUserStub, USER_ID_MIN } from '~~/stubs/create-user-stub'

export function createUserBillingUserStub (id: UserId = USER_ID_MIN): UserBillingUser {
  const user = createUserStub(id)
  const bankAccount = createBankAccountStub(user.bankAccountId)
  return {
    name: user.name,
    addr: user.addr,
    contacts: user.contacts,
    billingDestination: user.billingDestination,
    bankAccount
  }
}
