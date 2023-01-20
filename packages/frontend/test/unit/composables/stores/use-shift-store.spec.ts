/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { ShiftStore, useShiftStore } from '~/composables/stores/use-shift-store'
import { usePlugins } from '~/composables/use-plugins'
import { $datetime } from '~/services/datetime-service'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createShiftResponseStub } from '~~/stubs/create-shift-response-stub'
import { createShiftStub } from '~~/stubs/create-shift-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-shift-store', () => {
  const $api = createMockedApi('shifts')
  const plugins = createMockedPlugins({ $api })
  let store: ShiftStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useShiftStore()
    })

    it('should have a value', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('shift', () => {
      it('should be ref to undefined', () => {
        expect(store.state.shift).toBeRef()
        expect(store.state.shift.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createShiftResponseStub()

    beforeEach(() => {
      store = useShiftStore()
      jest.spyOn($api.shifts, 'get').mockResolvedValue(response)
    })

    afterEach(() => {
      mocked($api.shifts.get).mockReset()
    })

    it('should call $api.shifts.get', async () => {
      await store.get({ id })
      expect($api.shifts.get).toHaveBeenCalledTimes(1)
      expect($api.shifts.get).toHaveBeenCalledWith({ id })
    })

    it('should update state.shift', async () => {
      expect(store.state.shift.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.shift.value).toStrictEqual(response.shift)
    })
  })

  describe('update', () => {
    const id = 1
    const contractId = 100
    const current = createShiftResponseStub()
    const shift = createShiftStub(current.shift.id, createContractStub(contractId))
    const updated = { shift }
    const form = {
      task: shift.task,
      serviceCode: shift.serviceCode,
      officeId: shift.officeId,
      userId: shift.userId,
      contractId: shift.contractId,
      assignerId: shift.assignerId,
      assignees: shift.assignees,
      headcount: shift.headcount,
      schedule: {
        date: $datetime.parse(shift.schedule.date).toISODate(),
        start: $datetime.parse(shift.schedule.start).toFormat('HH:mm'),
        end: $datetime.parse(shift.schedule.end).toFormat('HH:mm')
      },
      durations: [...shift.durations],
      options: [...shift.options],
      note: shift.note
    }

    beforeAll(() => {
      store = useShiftStore()
      jest.spyOn($api.shifts, 'update').mockResolvedValue(updated)
      jest.spyOn($api.shifts, 'get').mockResolvedValue(current)
    })

    afterAll(() => {
      mocked($api.shifts.update).mockReset()
      mocked($api.shifts.get).mockReset()
    })

    beforeEach(async () => {
      await store.get({ id })
    })

    it('should call $api.shifts.update', async () => {
      await store.update({ form, id })
      expect($api.shifts.update).toHaveBeenCalledTimes(1)
      expect($api.shifts.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.shift', async () => {
      expect(store.state.shift.value).toStrictEqual(current.shift)
      await store.update({ form, id })
      expect(store.state.shift.value).toStrictEqual(updated.shift)
    })
  })
})
