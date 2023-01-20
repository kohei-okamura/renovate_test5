/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBaseIncreaseSupportAddition } from '@zinger/enums/lib/dws-base-increase-support-addition'
import { DwsSpecifiedTreatmentImprovementAddition } from '@zinger/enums/lib/dws-specified-treatment-improvement-addition'
import { DwsTreatmentImprovementAddition } from '@zinger/enums/lib/dws-treatment-improvement-addition'
import { VisitingCareForPwsdSpecifiedOfficeAddition } from '@zinger/enums/lib/visiting-care-for-pwsd-specified-office-addition'
import { range } from '@zinger/helpers'
import { OfficeId } from '~/models/office'
import { VisitingCareForPwsdCalcSpec, VisitingCareForPwsdCalcSpecId } from '~/models/visiting-care-for-pwsd-calc-spec'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const VISITING_CARE_FOR_PWSD_CALC_SPEC_ID_MAX = ID_MAX
export const VISITING_CARE_FOR_PWSD_CALC_SPEC_ID_MIN = ID_MIN

export function createVisitingCareForPwsdCalcSpecStub (
  id: VisitingCareForPwsdCalcSpecId = VISITING_CARE_FOR_PWSD_CALC_SPEC_ID_MIN
): VisitingCareForPwsdCalcSpec {
  const faker = createFaker(SEEDS[id - 1])
  const periodTimes = [faker.randomDateString(), faker.randomDateString()]
  const specifiedOfficeAddition = faker.randomElement(VisitingCareForPwsdSpecifiedOfficeAddition.values)
  const treatmentImprovementAddition = faker.randomElement(DwsTreatmentImprovementAddition.values)
  const specifiedTreatmentImprovementAddition = faker.randomElement(DwsSpecifiedTreatmentImprovementAddition.values)
  const baseIncreaseSupportAddition = faker.randomElement(DwsBaseIncreaseSupportAddition.values)
  return {
    id,
    officeId: faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX),
    period: {
      start: periodTimes[0] < periodTimes[1] ? periodTimes[0] : periodTimes[1],
      end: periodTimes[0] > periodTimes[1] ? periodTimes[0] : periodTimes[1]
    },
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

export function createVisitingCareForPwsdCalcSpecStubs (officeId: OfficeId): VisitingCareForPwsdCalcSpec[] {
  const count = createFaker(SEEDS[officeId - 1]).intBetween(0, 10)
  return range(0, count).map(i => {
    const id = createFaker(SEEDS[officeId + i]).intBetween(
      VISITING_CARE_FOR_PWSD_CALC_SPEC_ID_MIN, VISITING_CARE_FOR_PWSD_CALC_SPEC_ID_MAX
    )
    return createVisitingCareForPwsdCalcSpecStub(id)
  })
}
