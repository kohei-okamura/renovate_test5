/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { StaffsApi } from '~/services/api/staffs-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { createStaffStubs, STAFF_STUB_COUNT } from '~~/stubs/create-staff-stub'

export function createStaffIndexResponseStub (params: StaffsApi.GetIndexParams = {}): StaffsApi.GetIndexResponse {
  return createIndexResponse(params, STAFF_STUB_COUNT, createStaffStubs)
}
