/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ShiftId } from '~/models/shift'
import { ShiftsApi } from '~/services/api/shifts-api'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createShiftStub, SHIFT_ID_MIN } from '~~/stubs/create-shift-stub'

export function createShiftResponseStub (id: ShiftId = SHIFT_ID_MIN): ShiftsApi.GetResponse {
  const contractId = Math.floor(id / 10)
  const contract = createContractStub(contractId)
  const shift = createShiftStub(id, contract)
  return {
    shift
  }
}
