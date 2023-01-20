/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationType } from '@zinger/enums/lib/copay-coordination-type'
import { DwsCertificationAgreementType } from '@zinger/enums/lib/dws-certification-agreement-type'
import { DwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsLevel } from '@zinger/enums/lib/dws-level'
import { DwsType } from '@zinger/enums/lib/dws-type'
import { range } from '@zinger/helpers'
import { DwsCertification, DwsCertificationId } from '~/models/dws-certification'
import { UserId } from '~/models/user'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import { SEEDS, STUB_DEFAULT_SEED } from '~~/stubs/index'

export const DWS_CERTIFICATION_ID_MAX = USER_ID_MAX * 10 + 9
export const DWS_CERTIFICATION_ID_MIN = USER_ID_MIN * 10

export function createDwsCertificationStub (id: DwsCertificationId = DWS_CERTIFICATION_ID_MIN): DwsCertification {
  const userId = Math.floor(id / 10)
  const faker = createFaker(STUB_DEFAULT_SEED + `${userId}`.padStart(4, '0') + `${id}`.padStart(4, '0'))
  const fake = faker.createFake()
  return {
    id,
    userId,
    child: {
      name: fake.name,
      birthday: faker.randomDateString()
    },
    effectivatedOn: faker.randomDateString(),
    status: faker.randomElement(DwsCertificationStatus.values),
    dwsNumber: faker.randomNumericString(10),
    dwsTypes: faker.randomElements(DwsType.values, faker.intBetween(1, DwsType.size)),
    issuedOn: faker.randomDateString(),
    cityName: fake.addr.city,
    cityCode: faker.randomNumericString(5),
    dwsLevel: faker.randomElement(DwsLevel.values),
    isSubjectOfComprehensiveSupport: faker.randomBoolean(),
    activatedOn: faker.randomDateString(),
    deactivatedOn: faker.randomDateString(),
    grants: range(1, faker.intBetween(2, 5)).map(() => ({
      dwsCertificationServiceType: faker.randomElement(DwsCertificationServiceType.values),
      grantedAmount: faker.randomNumericString(3),
      activatedOn: faker.randomDateString(),
      deactivatedOn: faker.randomDateString()
    })),
    copayRate: faker.intBetween(0, 10) * 10,
    copayLimit: faker.intBetween(0, 10000),
    copayActivatedOn: faker.randomDateString(),
    copayDeactivatedOn: faker.randomDateString(),
    copayCoordination: {
      copayCoordinationType: faker.randomElement(CopayCoordinationType.values),
      officeId: faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)
    },
    agreements: range(1, faker.intBetween(2, 5)).map(indexNumber => ({
      indexNumber,
      officeId: faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX),
      dwsCertificationAgreementType: faker.randomElement(DwsCertificationAgreementType.values),
      paymentAmount: faker.intBetween(100, 5000) * 10,
      agreedOn: faker.randomDateString(),
      expiredOn: faker.randomDateString()
    })),
    isEnabled: true,
    version: id,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createDwsCertificationStubsForUser (userId: UserId): DwsCertification[] {
  const count = userId === USER_ID_MIN ? 4 : createFaker(SEEDS[userId - 1]).intBetween(0, 2)
  return range(0, count).map(i => createDwsCertificationStub(userId * 10 + i))
}
