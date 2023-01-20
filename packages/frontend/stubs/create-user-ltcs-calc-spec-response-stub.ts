/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserLtcsCalcSpecId } from '~/models/user-ltcs-calc-spec'
import { UserLtcsCalcSpecsApi } from '~/services/api/user-ltcs-calc-specs-api'
import { createUserLtcsCalcSpecStub, USER_LTCS_CALC_SPEC_ID_MIN } from '~~/stubs/create-user-ltcs-calc-spec-stub'

export function createUserLtcsCalcSpecResponseStub (
  id: UserLtcsCalcSpecId = USER_LTCS_CALC_SPEC_ID_MIN
): UserLtcsCalcSpecsApi.GetResponse {
  return {
    ltcsCalcSpec: createUserLtcsCalcSpecStub(id)
  }
}
