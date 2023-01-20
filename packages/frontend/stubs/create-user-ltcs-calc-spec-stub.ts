/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsUserLocationAddition } from '@zinger/enums/lib/ltcs-user-location-addition'
import { range } from '@zinger/helpers'
import { UserId } from '~/models/user'
import { UserLtcsCalcSpec, UserLtcsCalcSpecId } from '~/models/user-ltcs-calc-spec'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const USER_LTCS_CALC_SPEC_ID_MAX = ID_MAX
export const USER_LTCS_CALC_SPEC_ID_MIN = ID_MIN
export const USER_LTCS_CALC_SPEC_STUB_COUNT = USER_LTCS_CALC_SPEC_ID_MAX - USER_LTCS_CALC_SPEC_ID_MIN + 1

export function createUserLtcsCalcSpecStub (id: UserLtcsCalcSpecId = USER_LTCS_CALC_SPEC_ID_MIN): UserLtcsCalcSpec {
  const userId = Math.floor(id / 10)
  const faker = createFaker(SEEDS[id - 1])
  return {
    id,
    userId,
    effectivatedOn: faker.randomDateString(),
    locationAddition: faker.randomElement(LtcsUserLocationAddition.values),
    isEnabled: faker.randomBoolean(),
    version: 1,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createUserLtcsCalcSpecStubs: CreateStubs<UserLtcsCalcSpec> = (
  n = USER_LTCS_CALC_SPEC_STUB_COUNT,
  skip = 0
) => {
  const start = USER_LTCS_CALC_SPEC_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createUserLtcsCalcSpecStub)
}

export function createUserLtcsCalcSpecStubsForUser (userId: UserId): UserLtcsCalcSpec[] {
  const count = userId === USER_ID_MIN ? 5 : createFaker(SEEDS[userId - 1]).intBetween(0, 2)
  return range(0, count).map(i => createUserLtcsCalcSpecStub(userId * 10 + i))
}
