/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AttendanceId } from '~/models/attendance'
import { AttendancesApi } from '~/services/api/attendances-api'
import { ATTENDANCE_ID_MIN, createAttendanceStub } from '~~/stubs/create-attendance-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'

export function createAttendanceResponseStub (id: AttendanceId = ATTENDANCE_ID_MIN): AttendancesApi.GetResponse {
  const contractId = Math.floor(id / 10)
  const contract = createContractStub(contractId)
  const attendance = createAttendanceStub(id, contract)
  return {
    attendance
  }
}
