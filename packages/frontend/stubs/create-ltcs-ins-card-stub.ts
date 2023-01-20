/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { LtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { range } from '@zinger/helpers'
import { LtcsInsCard, LtcsInsCardId } from '~/models/ltcs-ins-card'
import { UserId } from '~/models/user'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { SEEDS, STUB_DEFAULT_SEED } from '~~/stubs/index'

export const LTCS_INS_CARD_ID_MAX = USER_ID_MAX * 10 + 9
export const LTCS_INS_CARD_ID_MIN = USER_ID_MIN * 10

export function createLtcsInsCardStub (id: LtcsInsCardId = LTCS_INS_CARD_ID_MIN): LtcsInsCard {
  const userId = Math.floor(id / 10)
  const faker = createFaker(STUB_DEFAULT_SEED + `${userId}`.padStart(4, '0') + `${id}`.padStart(4, '0'))
  const fake = faker.createFake()
  const ltcsLevel = faker.randomElement(LtcsLevel.values)
  const carePlanAuthorType = faker.randomElement(LtcsCarePlanAuthorType.values)
  return {
    id,
    userId,
    ltcsLevel,
    effectivatedOn: faker.randomDateString(),
    insNumber: faker.randomNumericString(10),
    issuedOn: faker.randomDateString(),
    insurerNumber: faker.randomNumericString(6),
    insurerName: fake.addr.city,
    status: faker.randomElement(LtcsInsCardStatus.values),
    certificatedOn: faker.randomDateString(),
    activatedOn: faker.randomDateString(),
    deactivatedOn: faker.randomDateString(),
    maxBenefitQuotas: range(0, faker.intBetween(0, 5)).map(() => ({
      ltcsInsCardServiceType: faker.randomElement(LtcsInsCardServiceType.values),
      maxBenefitQuota: faker.intBetween(497, 3583) * 100
    })),
    copayRate: faker.intBetween(0, 10) * 10,
    copayActivatedOn: faker.randomDateString(),
    copayDeactivatedOn: faker.randomDateString(),
    careManagerName: fake.name.displayName,
    carePlanAuthorType,
    communityGeneralSupportCenterId: [
      LtcsLevel.supportLevel1,
      LtcsLevel.supportLevel2,
      LtcsLevel.target
    ].includes(ltcsLevel as any)
      ? faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)
      : undefined,
    carePlanAuthorOfficeId: carePlanAuthorType === LtcsCarePlanAuthorType.careManagerOffice
      ? faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)
      : undefined,
    isEnabled: true,
    version: id,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createLtcsInsCardStubsForUser (userId: UserId): LtcsInsCard[] {
  const count = userId === USER_ID_MIN ? 4 : createFaker(SEEDS[userId - 1]).intBetween(0, 2)
  return range(0, count).map(i => createLtcsInsCardStub(userId * 10 + i))
}
