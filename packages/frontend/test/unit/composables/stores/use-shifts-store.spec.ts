/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { ShiftsStore, useShiftsStore } from '~/composables/stores/use-shifts-store'
import { usePlugins } from '~/composables/use-plugins'
import { createShiftIndexResponseStub } from '~~/stubs/create-shift-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-shifts-store', () => {
  const $api = createMockedApi('shifts')
  const plugins = createMockedPlugins({ $api })
  let store: ShiftsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useShiftsStore()
    })

    it('should have 4 values (4 states)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('shifts', () => {
      it('should be ref to empty array', () => {
        expect(store.state.shifts).toBeRef()
        expect(store.state.shifts.value).toBeEmptyArray()
      })
    })

    describe('isLoadingShifts', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingShifts).toBeRef()
        expect(store.state.isLoadingShifts.value).toBeFalse()
      })
    })

    describe('pagination', () => {
      it('should be ref to pagination object', () => {
        expect(store.state.pagination).toBeRef()
        expect(store.state.pagination.value).toStrictEqual({
          desc: false,
          page: 1,
          itemsPerPage: 10,
          sortBy: 'name'
        })
      })
    })

    describe('queryParams', () => {
      it('should be ref to undefined', () => {
        expect(store.state.queryParams).toBeRef()
        expect(store.state.queryParams.value).toBeUndefined()
      })
    })
  })

  describe('getIndex', () => {
    const response = createShiftIndexResponseStub()

    beforeEach(() => {
      store = useShiftsStore()
      jest.spyOn($api.shifts, 'getIndex').mockResolvedValue(response)
    })

    afterEach(() => {
      mocked($api.shifts.getIndex).mockReset()
    })

    it('should call $api.shifts.getIndex', async () => {
      await store.getIndex({ all: true })
      expect($api.shifts.getIndex).toHaveBeenCalledTimes(1)
      expect($api.shifts.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.shifts', async () => {
      expect(store.state.shifts.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.shifts.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingShifts', async () => {
      const deferred = new Deferred<typeof response>()
      mocked($api.shifts.getIndex).mockReturnValue(deferred.promise)
      expect(store.state.isLoadingShifts.value).toBeFalse()

      const promise = store.getIndex({ all: true })

      expect(store.state.isLoadingShifts.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingShifts.value).toBeFalse()
    })

    it('should update state.pagination', async () => {
      expect(store.state.pagination.value).not.toStrictEqual(response.pagination)
      await store.getIndex({ all: true })
      expect(store.state.pagination.value).toStrictEqual(response.pagination)
    })

    it('should update state.queryParams', async () => {
      expect(store.state.queryParams.value).not.toStrictEqual({ all: true })
      await store.getIndex({ all: true })
      expect(store.state.queryParams.value).toStrictEqual({ all: true })
    })
  })
})
