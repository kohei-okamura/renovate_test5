/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Activity } from '@zinger/enums/lib/activity'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Task } from '@zinger/enums/lib/task'
import { range } from '@zinger/helpers'
import { Range } from 'immutable'
import { DateTime } from 'luxon'
import { Assignee } from '~/models/assignee'
import { Contract } from '~/models/contract'
import { ISO_DATETIME_FORMAT } from '~/models/date'
import { duration } from '~/models/duration'
import { Shift, ShiftId } from '~/models/shift'
import { taskToActivity } from '~/models/task-utils'
import { $datetime } from '~/services/datetime-service'
import { CONTRACT_ID_MAX, CONTRACT_ID_MIN, createContractStubsForUser } from '~~/stubs/create-contract-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { STAFF_ID_MAX, STAFF_ID_MIN } from '~~/stubs/create-staff-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'
import { CreateStubs, STUB_COUNT, STUB_DEFAULT_SEED } from '~~/stubs/index'

export const SHIFT_ID_MAX = CONTRACT_ID_MAX * 10 + 9
export const SHIFT_ID_MIN = CONTRACT_ID_MIN * 10
export const SHIFT_STUB_COUNT = STUB_COUNT * 3

const reasons = [
  '担当者が体調不良のため',
  '先方からキャンセルの申し出があったため',
  '二重登録だったため',
  '誤登録だったため',
  'ダブルブッキングだったため'
]

export function createShiftStub (
  id: ShiftId,
  contract: Contract,
  dateBase: DateTime = $datetime.from(2019, 11, 30),
  dateRange: number = 31
): Shift {
  const seed = STUB_DEFAULT_SEED + `${id}`.padStart(6, '0')
  const faker = createFaker(seed)
  const ramen = ramenIpsum.factory(seed)
  const task = faker.randomElement(Task.values)
  const hasResting = faker.randomBoolean()
  const durations = [
    ...taskToActivity(task).map(activity => duration(activity, faker.intBetween(2, 12) * 30)),
    ...(hasResting ? [duration(Activity.resting, faker.intBetween(1, 2) * 30)] : [])
  ]
  const minutes = durations.map(x => x.duration).reduce((z, x) => z + x)
  const days = faker.intBetween(0, dateRange)
  const date = dateBase.plus({ days })
  const start = date.plus({ minutes: faker.intBetween(12, 24) * 30 })
  const end = start.plus({ minutes })
  const x = +task >= 900000 ? undefined : contract
  const headcount: 1 | 2 = faker.intBetween(1, 20) <= 15 ? 1 : 2
  const isLtcs = task === Task.ltcsPhysicalCare ||
    task === Task.ltcsHousework ||
    task === Task.ltcsPhysicalCareAndHousework
  const isCanceled = faker.randomBoolean()
  return {
    id,
    contractId: x?.id,
    officeId: faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX),
    userId: x?.userId,
    assignerId: faker.intBetween(STAFF_ID_MIN, STAFF_ID_MAX),
    task,
    serviceCode: isLtcs ? faker.randomNumericString(6) : '',
    headcount,
    assignees: [...Array(headcount)].map((): Assignee => {
      const isUndecided = faker.intBetween(1, 10) === 1
      return {
        staffId: isUndecided ? undefined : faker.intBetween(STAFF_ID_MIN, STAFF_ID_MAX),
        isUndecided,
        isTraining: faker.randomBoolean()
      }
    }),
    schedule: {
      date: date.toISODate(),
      start: start.toFormat(ISO_DATETIME_FORMAT),
      end: end.toFormat(ISO_DATETIME_FORMAT)
    },
    durations,
    options: faker.randomElements(ServiceOption.values, faker.intBetween(0, ServiceOption.size)),
    note: ramen.ipsum(20),
    isConfirmed: faker.randomBoolean(),
    isCanceled,
    reason: isCanceled ? reasons[faker.intBetween(0, reasons.length - 1)] : undefined,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createShiftStubsForContract (
  contract: Contract,
  dateBase: DateTime = $datetime.from(2018, 5, 17),
  dateRange: number = 31
): Shift[] {
  return range(0, 2).map(i => createShiftStub(contract.id * 10 + i, contract, dateBase, dateRange))
}

export const createShiftStubs: CreateStubs<Shift> = (n = SHIFT_STUB_COUNT, skip = 0) => {
  return Range(USER_ID_MIN, USER_ID_MAX)
    .flatMap(createContractStubsForUser)
    .flatMap(x => createShiftStubsForContract(x))
    .skip(skip)
    .take(n)
    .toArray()
}
