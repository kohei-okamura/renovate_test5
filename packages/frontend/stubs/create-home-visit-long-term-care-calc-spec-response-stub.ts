/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HomeVisitLongTermCareCalcSpecId } from '~/models/home-visit-long-term-care-calc-spec'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'
import {
  createHomeVisitLongTermCareCalcSpecStub,
  HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ID_MIN
} from '~~/stubs/create-home-visit-long-term-care-calc-spec-stub'

export function createHomeVisitLongTermCareCalcSpecResponseStub (
  id: HomeVisitLongTermCareCalcSpecId = HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ID_MIN
): HomeVisitLongTermCareCalcSpecsApi.GetResponse {
  return {
    homeVisitLongTermCareCalcSpec: createHomeVisitLongTermCareCalcSpecStub(id)
  }
}

export function createHomeVisitLongTermCareCalcSpecPostOrPutResponseStub (
  id: HomeVisitLongTermCareCalcSpecId = HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ID_MIN
): HomeVisitLongTermCareCalcSpecsApi.UpdateResponse {
  return {
    homeVisitLongTermCareCalcSpec: createHomeVisitLongTermCareCalcSpecStub(id),
    provisionReportCount: Math.floor(Math.random() * 3)
  }
}
