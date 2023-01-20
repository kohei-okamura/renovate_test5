/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AttendancesApi } from '~/services/api/attendances-api'
import { ATTENDANCE_STUB_COUNT, createAttendanceStubs } from '~~/stubs/create-attendance-stub'
import { createIndexResponse } from '~~/stubs/create-index-response'

export function createAttendanceIndexResponseStub (
  params: AttendancesApi.GetIndexParams = {}
): AttendancesApi.GetIndexResponse {
  return createIndexResponse(params, ATTENDANCE_STUB_COUNT, createAttendanceStubs)
}
