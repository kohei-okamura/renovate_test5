/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { StaffId } from '~/models/staff'
import { StaffsApi } from '~/services/api/staffs-api'
import { createBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createRoleStub } from '~~/stubs/create-role-stub'
import { createStaffStub, STAFF_ID_MIN } from '~~/stubs/create-staff-stub'

export function createStaffResponseStub (id: StaffId = STAFF_ID_MIN): StaffsApi.GetResponse {
  const staff = createStaffStub(id)
  return {
    bankAccount: createBankAccountStub(staff.bankAccountId),
    offices: staff.officeIds.map(createOfficeStub),
    roles: staff.roleIds.map(createRoleStub),
    staff
  }
}
