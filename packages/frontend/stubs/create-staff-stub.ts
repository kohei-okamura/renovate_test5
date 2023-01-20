/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Certification } from '@zinger/enums/lib/certification'
import { StaffStatus } from '@zinger/enums/lib/staff-status'
import { range } from '@zinger/helpers'
import { Staff, StaffId } from '~/models/staff'
import { BANK_ID_MAX, BANK_ID_MIN } from '~~/stubs/create-bank-account-stub'
import { OFFICE_GROUP_IDS } from '~~/stubs/create-office-group-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { ROLE_ID_MAX, ROLE_ID_MIN } from '~~/stubs/create-role-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, ID_MAX, ID_MIN, SEEDS, STUB_COUNT } from '~~/stubs/index'

const roleIds = range(ROLE_ID_MIN, ROLE_ID_MAX)

export const STAFF_ID_MAX = ID_MAX
export const STAFF_ID_MIN = ID_MIN
export const STAFF_STUB_COUNT = STUB_COUNT

export function createStaffStub (id: StaffId = STAFF_ID_MIN): Staff {
  const faker = createFaker(SEEDS[id - 1])
  const fake = faker.createFake()
  return {
    id,
    employeeNumber: String(id).padStart(4, '0'),
    name: fake.name,
    sex: fake.sex,
    birthday: faker.randomDateString(),
    addr: fake.addr,
    // TODO: 緯度・経度をランダムに設定する.
    location: {
      lat: 0.0,
      lng: 0.0
    },
    tel: fake.tel,
    fax: fake.fax,
    email: fake.email,
    bankAccountId: faker.intBetween(BANK_ID_MIN, BANK_ID_MAX),
    certifications: id !== 1 && faker.randomBoolean()
      ? []
      : Certification.values.filter(() => faker.randomBoolean()),
    roleIds: id === 1 ? range(ROLE_ID_MIN, ROLE_ID_MAX) : roleIds.filter(() => faker.randomBoolean()),
    officeIds: range(0, faker.intBetween(0, 2)).map(() => faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)),
    officeGroupIds: range(0, faker.intBetween(0, 2)).map(() => faker.randomElement(OFFICE_GROUP_IDS)),
    isVerified: true,
    status: faker.randomElement(StaffStatus.values),
    isEnabled: true,
    version: 1,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createStaffStubs: CreateStubs<Staff> = (n = STAFF_STUB_COUNT, skip = 0) => {
  const start = STAFF_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createStaffStub)
}
