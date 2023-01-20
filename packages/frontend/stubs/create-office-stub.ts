/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { Purpose } from '@zinger/enums/lib/purpose'
import { range } from '@zinger/helpers'
import toKatakana from 'jaco/fn/toKatakana'
import { Office, OfficeId } from '~/models/office'
import { DWS_AREA_GRADE_ID_MAX, DWS_AREA_GRADE_ID_MIN } from '~~/stubs/create-dws-area-grade-stub'
import { LTCS_AREA_GRADE_ID_MAX, LTCS_AREA_GRADE_ID_MIN } from '~~/stubs/create-ltcs-area-grade-stub'
import { OFFICE_GROUP_IDS } from '~~/stubs/create-office-group-stub'
import { createFaker } from '~~/stubs/fake'
import { officeNames } from '~~/stubs/fake/office-names'
import { CreateStubs, ID_MIN, SEEDS } from '~~/stubs/index'

export const OFFICE_ID_MAX = ID_MIN + officeNames.length - 1
export const OFFICE_ID_MIN = ID_MIN
export const OFFICE_STUB_COUNT = OFFICE_ID_MAX - OFFICE_ID_MIN + 1

export function createOfficeStub (id: OfficeId = OFFICE_ID_MIN): Office {
  const { name, abbr, phoneticName } = officeNames[id - 1]
  const faker = createFaker(SEEDS[id - 1])
  const fake = faker.createFake()
  const purpose = id === OFFICE_ID_MIN ? Purpose.internal : faker.randomElement([Purpose.internal, Purpose.external])
  const dwsGenericService = faker.randomElement([{
    code: faker.randomNumericString(10),
    openedOn: faker.randomDateString(),
    designationExpiredOn: faker.randomDateString(),
    dwsAreaGradeId: faker.intBetween(DWS_AREA_GRADE_ID_MIN, DWS_AREA_GRADE_ID_MAX)
  }, undefined])
  const dwsCommAccompanyService = faker.randomElement([{
    code: faker.randomNumericString(10),
    openedOn: faker.randomDateString(),
    designationExpiredOn: faker.randomDateString()
  }, undefined])
  const ltcsHomeVisitLongTermCareService = faker.randomElement([{
    code: faker.randomNumericString(10),
    openedOn: faker.randomDateString(),
    designationExpiredOn: faker.randomDateString(),
    ltcsAreaGradeId: faker.intBetween(LTCS_AREA_GRADE_ID_MIN, LTCS_AREA_GRADE_ID_MAX)
  }, undefined])
  const ltcsCareManagementService = faker.randomElement([{
    code: faker.randomNumericString(10),
    openedOn: faker.randomDateString(),
    designationExpiredOn: faker.randomDateString(),
    ltcsAreaGradeId: faker.intBetween(LTCS_AREA_GRADE_ID_MIN, LTCS_AREA_GRADE_ID_MAX)
  }, undefined])
  const ltcsCompHomeVisitingService = faker.randomElement([{
    code: faker.randomNumericString(10),
    openedOn: faker.randomDateString(),
    designationExpiredOn: faker.randomDateString()
  }, undefined])
  const ltcsPreventionService = faker.randomElement([{
    code: faker.randomNumericString(10),
    openedOn: faker.randomDateString(),
    designationExpiredOn: faker.randomDateString()
  }, undefined])

  return {
    id,
    name,
    abbr,
    phoneticName: toKatakana(phoneticName),
    corporationName: Purpose.internal ? '' : name, // 仮
    phoneticCorporationName: Purpose.internal ? '' : toKatakana(phoneticName), // 仮
    purpose,
    addr: fake.addr,
    location: fake.location,
    tel: fake.tel,
    fax: fake.fax,
    email: fake.email,
    qualifications: faker.randomElements(OfficeQualification.values, faker.intBetween(0, OfficeQualification.size)),
    officeGroupId: purpose === Purpose.internal ? faker.randomElement(OFFICE_GROUP_IDS) : undefined,
    dwsGenericService,
    dwsCommAccompanyService,
    ltcsHomeVisitLongTermCareService,
    ltcsCareManagementService,
    ltcsCompHomeVisitingService,
    ltcsPreventionService,
    status: faker.randomElement(OfficeStatus.values),
    isEnabled: true,
    version: 1,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createOfficeStubs: CreateStubs<Office> = (n = OFFICE_STUB_COUNT, skip = 0) => {
  const start = OFFICE_ID_MIN + skip
  const end = Math.min(start + n - 1, OFFICE_ID_MAX)
  return range(start, end).map(createOfficeStub)
}
