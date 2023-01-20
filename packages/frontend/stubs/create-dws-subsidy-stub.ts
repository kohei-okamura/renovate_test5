/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { range } from '@zinger/helpers'
import { UserId } from '~/models/user'
import { UserDwsSubsidy, UserDwsSubsidyId } from '~/models/user-dws-subsidy'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { SEEDS, STUB_DEFAULT_SEED } from '~~/stubs/index'

export const DWS_SUBSIDY_ID_MIN = USER_ID_MIN * 10

export function createDwsSubsidyStub (
  id: UserDwsSubsidyId = DWS_SUBSIDY_ID_MIN,
  subsidyType: UserDwsSubsidyType = UserDwsSubsidyType.benefitRate
) {
  const userId = Math.floor(id / 10)
  const faker = createFaker(STUB_DEFAULT_SEED + `${userId}`.padStart(4, '0') + `${id}`.padStart(4, '0') + `${id}`.padStart(4, '0'))
  const periodTimes = [faker.randomDateString(), faker.randomDateString()]
  return {
    id,
    userId,
    period: {
      start: periodTimes[0] < periodTimes[1] ? periodTimes[0] : periodTimes[1],
      end: periodTimes[0] > periodTimes[1] ? periodTimes[0] : periodTimes[1]
    },
    cityName: faker.createFake().addr.city,
    cityCode: faker.randomNumericString(6),
    subsidyType,
    factor: faker.randomElement(UserDwsSubsidyFactor.values),
    benefitRate: subsidyType === 1 ? faker.intBetween(1, 100) : 0,
    copayRate: subsidyType === 1 ? faker.intBetween(1, 100) : 0,
    rounding: faker.randomElement(Rounding.values),
    benefitAmount: subsidyType === 2 ? faker.intBetween(1000, 2000) : 0,
    copayAmount: subsidyType === 3 ? faker.intBetween(1000, 2000) : 0,
    note: '備考',
    isEnabled: true,
    version: id,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createDwsSubsidiesStub (userId: UserId): UserDwsSubsidy[] {
  const count = userId === USER_ID_MIN ? 4 : createFaker(SEEDS[userId - 1]).intBetween(0, 2)
  return range(0, count).map(i => createDwsSubsidyStub(userId * 10 + i))
}
