/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { range } from '@zinger/helpers/index'
import { DwsBillingUser } from '~/models/dws-billing-user'
import { DWS_CERTIFICATION_ID_MAX, DWS_CERTIFICATION_ID_MIN } from '~~/stubs/create-dws-certification-stub'
import { createUserStub, USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, SEEDS } from '~~/stubs/index'

export const DWS_BILLING_USER_STUB_COUNT = 10

export function createDwsBillingUserStub (id: DwsBillingUser['userId'] = USER_ID_MIN): DwsBillingUser {
  const faker = createFaker(SEEDS[id - 1])
  const user = createUserStub(id ?? faker.intBetween(USER_ID_MIN, USER_ID_MAX))
  const child = createUserStub(faker.intBetween(USER_ID_MIN, USER_ID_MAX))
  return {
    userId: user.id,
    dwsCertificationId: faker.intBetween(DWS_CERTIFICATION_ID_MIN, DWS_CERTIFICATION_ID_MAX),
    dwsNumber: faker.randomNumericString(10),
    name: user.name,
    childName: child.name,
    copayLimit: faker.intBetween(10000, 99999)
  }
}

export const createDwsBillingUserStubs: CreateStubs<DwsBillingUser> = (
  n = DWS_BILLING_USER_STUB_COUNT,
  skip = 0
) => {
  const start = USER_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createDwsBillingUserStub)
}
