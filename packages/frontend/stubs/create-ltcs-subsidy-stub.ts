/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DefrayerCategory } from '@zinger/enums/lib/defrayer-category'
import { range } from '@zinger/helpers'
import { UserId } from '~/models/user'
import { UserLtcsSubsidy, UserLtcsSubsidyId } from '~/models/user-ltcs-subsidy'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { SEEDS, STUB_DEFAULT_SEED } from '~~/stubs/index'

export const LTCS_SUBSIDY_ID_MAX = USER_ID_MAX * 10 + 9
export const LTCS_SUBSIDY_ID_MIN = USER_ID_MIN * 10

export function createLtcsSubsidyStub (id: UserLtcsSubsidyId = LTCS_SUBSIDY_ID_MIN): UserLtcsSubsidy {
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
    defrayerCategory: faker.randomElement([DefrayerCategory.atomicBombVictim, DefrayerCategory.pwdSupport]),
    defrayerNumber: faker.randomNumericString(8),
    recipientNumber: faker.randomNumericString(7),
    benefitRate: faker.intBetween(1, 100),
    copay: faker.intBetween(1000, 2000),
    isEnabled: true,
    version: id,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createLtcsSubsidyStubsForUser (userId: UserId): UserLtcsSubsidy[] {
  const count = userId === USER_ID_MIN ? 4 : createFaker(SEEDS[userId - 1]).intBetween(0, 2)
  return range(0, count).map(i => createLtcsSubsidyStub(userId * 10 + i))
}
