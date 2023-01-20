/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { AttendancesStore, useAttendancesStore } from '~/composables/stores/use-attendances-store'
import { usePlugins } from '~/composables/use-plugins'
import { createAttendanceIndexResponseStub } from '~~/stubs/create-attendance-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-attendances-store', () => {
  const $api = createMockedApi('attendances')
  const plugins = createMockedPlugins({ $api })
  let store: AttendancesStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useAttendancesStore()
    })

    it('should have 4 values (4 states)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('attendances', () => {
      it('should be ref to empty array', () => {
        expect(store.state.attendances).toBeRef()
        expect(store.state.attendances.value).toBeEmptyArray()
      })
    })

    describe('isLoadingAttendances', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingAttendances).toBeRef()
        expect(store.state.isLoadingAttendances.value).toBeFalse()
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
    const response = createAttendanceIndexResponseStub()

    beforeEach(() => {
      store = useAttendancesStore()
      jest.spyOn($api.attendances, 'getIndex').mockResolvedValue(response)
    })

    afterEach(() => {
      mocked($api.attendances.getIndex).mockReset()
    })

    it('should call $api.attendances.getIndex', async () => {
      await store.getIndex({ all: true })
      expect($api.attendances.getIndex).toHaveBeenCalledTimes(1)
      expect($api.attendances.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.attendances', async () => {
      expect(store.state.attendances.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.attendances.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingAttendances', async () => {
      const deferred = new Deferred<typeof response>()
      mocked($api.attendances.getIndex).mockReturnValue(deferred.promise)
      expect(store.state.isLoadingAttendances.value).toBeFalse()

      const promise = store.getIndex({ all: true })

      expect(store.state.isLoadingAttendances.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingAttendances.value).toBeFalse()
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
