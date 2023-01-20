/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Attendance, AttendanceId } from '~/models/attendance'
import { Contract } from '~/models/contract'
import { Shift } from '~/models/shift'
import {
  createShiftStub,
  createShiftStubs,
  createShiftStubsForContract,
  SHIFT_ID_MAX,
  SHIFT_ID_MIN,
  SHIFT_STUB_COUNT
} from '~~/stubs/create-shift-stub'
import { CreateStubs } from '~~/stubs/index'

export const ATTENDANCE_ID_MAX = SHIFT_ID_MAX
export const ATTENDANCE_ID_MIN = SHIFT_ID_MIN
export const ATTENDANCE_STUB_COUNT = SHIFT_STUB_COUNT

export const createAttendanceStubFromShift = (shift: Shift): Attendance => ({
  ...shift,
  shiftId: shift.id
})

export function createAttendanceStub (id: AttendanceId, contract: Contract): Attendance {
  return createAttendanceStubFromShift(createShiftStub(id, contract))
}

export function createAttendanceStubsForContract (contract: Contract): Attendance[] {
  return createShiftStubsForContract(contract).map(createAttendanceStubFromShift)
}

export const createAttendanceStubs: CreateStubs<Attendance> = (n = ATTENDANCE_STUB_COUNT, skip = 0) => {
  return createShiftStubs(n, skip).map(createAttendanceStubFromShift)
}
