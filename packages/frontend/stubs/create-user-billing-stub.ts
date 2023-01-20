/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { WithdrawalResultCode } from '@zinger/enums/lib/withdrawal-result-code'
import { range } from '@zinger/helpers'
import { UserBilling, UserBillingId } from '~/models/user-billing'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUserBillingDwsItemStub } from '~~/stubs/create-user-billing-dws-item-stub'
import { createUserBillingLtcsItemStub } from '~~/stubs/create-user-billing-ltcs-item-stub'
import { createUserBillingOfficeStub } from '~~/stubs/create-user-billing-office-stub'
import { createUserBillingOtherItemStub } from '~~/stubs/create-user-billing-other-item-stub'
import { createUserBillingUserStub } from '~~/stubs/create-user-billing-user-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, ID_MAX, ID_MIN, STUB_COUNT, STUB_DEFAULT_SEED } from '~~/stubs/index'

export const USER_BILLING_ID_MAX = ID_MAX
export const USER_BILLING_ID_MIN = ID_MIN
export const USER_BILLING_STUB_COUNT = STUB_COUNT

export function createUserBillingStub (id: UserBillingId = USER_BILLING_ID_MIN): UserBilling {
  const seed = STUB_DEFAULT_SEED + `${id}`.padStart(6, '0')
  const faker = createFaker(seed)
  const createdAt = faker.randomDateTimeString()
  const userId = faker.intBetween(USER_ID_MIN, USER_ID_MAX)
  const officeId = faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)
  const dwsItem = createUserBillingDwsItemStub(faker.intBetween(1, userId))
  const ltcsItem = createUserBillingLtcsItemStub(faker.intBetween(1, userId))
  const otherItems = range(1, 3).map(_ => createUserBillingOtherItemStub())
  const totalAmount = [...otherItems, dwsItem, ltcsItem].reduce((acc, cur) => acc + cur.totalAmount, 0)
  return {
    id,
    userId,
    officeId,
    user: createUserBillingUserStub(userId),
    office: createUserBillingOfficeStub(officeId),
    dwsItem: faker.intBetween(1, 10) % 3 === 0 ? undefined : dwsItem,
    ltcsItem: faker.intBetween(1, 10) % 3 === 0 ? undefined : ltcsItem,
    otherItems,
    result: faker.randomElement(UserBillingResult.values),
    totalAmount,
    carriedOverAmount: faker.intBetween(0, 100),
    withdrawalResultCode: id % 3 === 1 ? faker.randomElement(WithdrawalResultCode.values) : undefined,
    providedIn: faker.randomDateTimeString(),
    issuedOn: createdAt,
    depositedAt: faker.randomDateTimeString(),
    transactedAt: faker.randomDateTimeString(),
    deductedOn: faker.randomDateTimeString(),
    dueDate: faker.randomDateTimeString(),
    createdAt,
    updatedAt: faker.randomDateTimeString()
  }
}

export const createUserBillingStubs: CreateStubs<UserBilling> = (
  n = USER_BILLING_STUB_COUNT,
  skip = 0
) => {
  const start = USER_BILLING_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createUserBillingStub)
}
