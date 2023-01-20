/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HomeVisitLongTermCareSpecifiedOfficeAddition } from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { LtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { LtcsSpecifiedTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-specified-treatment-improvement-addition'
import { LtcsTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-treatment-improvement-addition'
import { range } from '@zinger/helpers'
import {
  HomeVisitLongTermCareCalcSpec,
  HomeVisitLongTermCareCalcSpecId
} from '~/models/home-visit-long-term-care-calc-spec'
import { OfficeId } from '~/models/office'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ID_MAX = ID_MAX
export const HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ID_MIN = ID_MIN

export function createHomeVisitLongTermCareCalcSpecStub (
  id: HomeVisitLongTermCareCalcSpecId = HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ID_MIN
): HomeVisitLongTermCareCalcSpec {
  const faker = createFaker(SEEDS[id - 1])
  const periodTimes = [faker.randomDateString(), faker.randomDateString()]
  const locationAddition = faker.randomElement(LtcsOfficeLocationAddition.values)
  const specifiedOfficeAddition = faker.randomElement(HomeVisitLongTermCareSpecifiedOfficeAddition.values)
  const treatmentImprovementAddition = faker.randomElement(LtcsTreatmentImprovementAddition.values)
  const specifiedTreatmentImprovementAddition = faker.randomElement(LtcsSpecifiedTreatmentImprovementAddition.values)
  const baseIncreaseSupportAddition = faker.randomElement(LtcsBaseIncreaseSupportAddition.values)
  return {
    id,
    officeId: faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX),
    period: {
      start: periodTimes[0] < periodTimes[1] ? periodTimes[0] : periodTimes[1],
      end: periodTimes[0] > periodTimes[1] ? periodTimes[0] : periodTimes[1]
    },
    locationAddition,
    specifiedOfficeAddition,
    treatmentImprovementAddition,
    specifiedTreatmentImprovementAddition,
    baseIncreaseSupportAddition,
    isEnabled: true,
    version: id,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createHomeVisitLongTermCareCalcSpecStubs (officeId: OfficeId): HomeVisitLongTermCareCalcSpec[] {
  const count = createFaker(SEEDS[officeId - 1]).intBetween(0, 10)
  return range(0, count).map(i => {
    const id = createFaker(SEEDS[officeId + i]).intBetween(
      HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ID_MIN, HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ID_MAX
    )
    return createHomeVisitLongTermCareCalcSpecStub(id)
  })
}
