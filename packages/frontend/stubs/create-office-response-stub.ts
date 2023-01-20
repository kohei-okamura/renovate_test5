/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Office, OfficeId } from '~/models/office'
import { OfficesApi } from '~/services/api/offices-api'
import { createHomeHelpServiceCalcSpecStubs } from '~~/stubs/create-home-help-service-calc-spec-stub'
import { createHomeVisitLongTermCareCalcSpecStubs } from '~~/stubs/create-home-visit-long-term-care-calc-spec-stub'
import { createOfficeGroupStub } from '~~/stubs/create-office-group-stub'
import { createOfficeStub, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createVisitingCareForPwsdCalcSpecStubs } from '~~/stubs/create-visiting-care-for-pwsd-calc-spec-stub'

export function createOfficeResponseStub (id: OfficeId = OFFICE_ID_MIN, office?: Office): OfficesApi.GetResponse {
  const newOffice = office ?? createOfficeStub(id)
  const newId = newOffice.id
  const officeGroup = createOfficeGroupStub(newOffice.officeGroupId)
  const homeHelpServiceCalcSpecs =
    newId === OFFICE_ID_MIN + 3 ? [] : createHomeHelpServiceCalcSpecStubs(newId)
  const homeVisitLongTermCareCalcSpecs =
    newId === OFFICE_ID_MIN + 3 ? [] : createHomeVisitLongTermCareCalcSpecStubs(newId)
  const visitingCareForPwsdCalcSpecs =
    newId === OFFICE_ID_MIN + 3 ? [] : createVisitingCareForPwsdCalcSpecStubs(newId)
  return {
    homeHelpServiceCalcSpecs,
    homeVisitLongTermCareCalcSpecs,
    visitingCareForPwsdCalcSpecs,
    office: newOffice,
    officeGroup
  }
}
