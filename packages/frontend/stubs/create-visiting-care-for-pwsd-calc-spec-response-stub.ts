/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { VisitingCareForPwsdCalcSpecId } from '~/models/visiting-care-for-pwsd-calc-spec'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'
import {
  createVisitingCareForPwsdCalcSpecStub,
  VISITING_CARE_FOR_PWSD_CALC_SPEC_ID_MIN
} from '~~/stubs/create-visiting-care-for-pwsd-calc-spec-stub'

export function createVisitingCareForPwsdCalcSpecResponseStub (
  id: VisitingCareForPwsdCalcSpecId = VISITING_CARE_FOR_PWSD_CALC_SPEC_ID_MIN
): VisitingCareForPwsdCalcSpecsApi.GetResponse {
  return {
    visitingCareForPwsdCalcSpec: createVisitingCareForPwsdCalcSpecStub(id)
  }
}
