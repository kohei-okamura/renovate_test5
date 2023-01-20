/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserDwsCalcSpecId } from '~/models/user-dws-calc-spec'
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'
import { createUserDwsCalcSpecStub, USER_DWS_CALC_SPEC_ID_MIN } from '~~/stubs/create-user-dws-calc-spec-stub'

export function createUserDwsCalcSpecResponseStub (
  id: UserDwsCalcSpecId = USER_DWS_CALC_SPEC_ID_MIN
): UserDwsCalcSpecsApi.GetResponse {
  return {
    dwsCalcSpec: createUserDwsCalcSpecStub(id)
  }
}
