/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Range } from 'immutable'
import { ShiftsApi } from '~/services/api/shifts-api'
import { $datetime } from '~/services/datetime-service'
import { createContractStubsForUser } from '~~/stubs/create-contract-stub'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { createShiftStubs, createShiftStubsForContract, SHIFT_STUB_COUNT } from '~~/stubs/create-shift-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'

function createShiftStubsForDashboard () {
  return Range(USER_ID_MIN, USER_ID_MAX)
    .flatMap(createContractStubsForUser)
    .flatMap(x => createShiftStubsForContract(x, $datetime.now.startOf('day'), 14))
    .take(30)
    .toArray()
}

export function createShiftIndexResponseStub (params: ShiftsApi.GetIndexParams = {}): ShiftsApi.GetIndexResponse {
  if (params.all) {
    const dummyParams = {
      ...params,
      all: false
    }
    return createIndexResponse(dummyParams, SHIFT_STUB_COUNT, createShiftStubsForDashboard)
  } else {
    return createIndexResponse(params, SHIFT_STUB_COUNT, createShiftStubs)
  }
}
