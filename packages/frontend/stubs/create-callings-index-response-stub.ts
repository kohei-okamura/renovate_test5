/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Shift } from '~/models/shift'
import { Api } from '~/services/api/core'
import { ShiftsApi } from '~/services/api/shifts-api'
import { createCallingsStub } from '~~/stubs/create-callings-stub'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { SHIFT_STUB_COUNT } from '~~/stubs/create-shift-stub'

export function createCallingIndexResponseStub (params: ShiftsApi.GetIndexParams = {}): Api.GetIndexResponse<Shift> {
  return createIndexResponse(params, SHIFT_STUB_COUNT, createCallingsStub)
}
