/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { AttendanceStore, useAttendanceStore } from '~/composables/stores/use-attendance-store'
import { usePlugins } from '~/composables/use-plugins'
import { $datetime } from '~/services/datetime-service'
import { createAttendanceResponseStub } from '~~/stubs/create-attendance-response-stub'
import { createAttendanceStub } from '~~/stubs/create-attendance-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-attendance-store', () => {
  const $api = createMockedApi('attendances')
  const plugins = createMockedPlugins({ $api })
  let store: AttendanceStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useAttendanceStore()
    })

    it('should have a value', () => {
      mocked(usePlugins).mockReturnValue(plugins)
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('attendance', () => {
      it('should be ref to undefined', () => {
        expect(store.state.attendance).toBeRef()
        expect(store.state.attendance.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createAttendanceResponseStub()

    beforeEach(() => {
      store = useAttendanceStore()
      jest.spyOn($api.attendances, 'get').mockResolvedValue(response)
    })

    afterEach(() => {
      mocked($api.attendances.get).mockReset()
    })

    it('should call $api.attendances.get', async () => {
      await store.get({ id })
      expect($api.attendances.get).toHaveBeenCalledTimes(1)
      expect($api.attendances.get).toHaveBeenCalledWith({ id })
    })

    it('should update state.attendance', async () => {
      expect(store.state.attendance.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.attendance.value).toStrictEqual(response.attendance)
    })
  })

  describe('update', () => {
    const id = 1
    const contractId = 100
    const current = createAttendanceResponseStub()
    const attendance = createAttendanceStub(current.attendance.id, createContractStub(contractId))
    const updated = { attendance }
    const form = {
      task: attendance.task,
      serviceCode: attendance.serviceCode,
      officeId: attendance.officeId,
      userId: attendance.userId,
      contractId: attendance.contractId,
      assignerId: attendance.assignerId,
      assignees: attendance.assignees,
      headcount: attendance.headcount,
      schedule: {
        date: $datetime.parse(attendance.schedule.date).toISODate(),
        start: $datetime.parse(attendance.schedule.start).toFormat('HH:mm'),
        end: $datetime.parse(attendance.schedule.end).toFormat('HH:mm')
      },
      durations: [...attendance.durations],
      options: [...attendance.options],
      note: attendance.note
    }

    beforeAll(() => {
      store = useAttendanceStore()
      jest.spyOn($api.attendances, 'get').mockResolvedValue(current)
      jest.spyOn($api.attendances, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id })
    })

    it('should call $api.attendances.update', async () => {
      await store.update({ form, id })
      expect($api.attendances.update).toHaveBeenCalledTimes(1)
      expect($api.attendances.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.attendance', async () => {
      expect(store.state.attendance.value).toStrictEqual(current.attendance)
      await store.update({ form, id })
      expect(store.state.attendance.value).toStrictEqual(updated.attendance)
    })
  })
})
