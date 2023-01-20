/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsUserLocationAddition } from '@zinger/enums/lib/dws-user-location-addition'
import { range } from '@zinger/helpers'
import { UserId } from '~/models/user'
import { UserDwsCalcSpec, UserDwsCalcSpecId } from '~/models/user-dws-calc-spec'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const USER_DWS_CALC_SPEC_ID_MAX = ID_MAX
export const USER_DWS_CALC_SPEC_ID_MIN = ID_MIN
export const USER_DWS_CALC_SPEC_STUB_COUNT = USER_DWS_CALC_SPEC_ID_MAX - USER_DWS_CALC_SPEC_ID_MIN + 1

export function createUserDwsCalcSpecStub (id: UserDwsCalcSpecId = USER_DWS_CALC_SPEC_ID_MIN): UserDwsCalcSpec {
  const userId = Math.floor(id / 10)
  const faker = createFaker(SEEDS[id - 1])
  return {
    id,
    userId,
    effectivatedOn: faker.randomDateString(),
    locationAddition: DwsUserLocationAddition.specifiedArea,
    isEnabled: faker.randomBoolean(),
    version: 1,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createUserDwsCalcSpecStubs: CreateStubs<UserDwsCalcSpec> = (
  n = USER_DWS_CALC_SPEC_STUB_COUNT,
  skip = 0
) => {
  const start = USER_DWS_CALC_SPEC_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createUserDwsCalcSpecStub)
}

export function createUserDwsCalcSpecStubsForUser (userId: UserId): UserDwsCalcSpec[] {
  const count = userId === USER_ID_MIN ? 5 : createFaker(SEEDS[userId - 1]).intBetween(0, 2)
  return range(0, count).map(i => createUserDwsCalcSpecStub(userId * 10 + i))
}
