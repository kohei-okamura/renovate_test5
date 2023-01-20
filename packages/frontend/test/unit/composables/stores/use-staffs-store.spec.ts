/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { StaffsStore, useStaffsStore } from '~/composables/stores/use-staffs-store'
import { usePlugins } from '~/composables/use-plugins'
import { createStaffIndexResponseStub } from '~~/stubs/create-staff-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-staffs-store', () => {
  const $api = createMockedApi('staffs')
  const plugins = createMockedPlugins({ $api })
  let store: StaffsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useStaffsStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('staffs', () => {
      it('should be ref to empty array', () => {
        expect(store.state.staffs).toBeRef()
        expect(store.state.staffs.value).toBeEmptyArray()
      })
    })

    describe('isLoadingStaffs', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingStaffs).toBeRef()
        expect(store.state.isLoadingStaffs.value).toBeFalse()
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
    const response = createStaffIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.staffs, 'getIndex').mockResolvedValue(response)
      store = useStaffsStore()
    })

    afterEach(() => {
      mocked($api.staffs.getIndex).mockReset()
    })

    it('should call $api.staffs.getIndex', async () => {
      await store.getIndex({ all: true })
      expect($api.staffs.getIndex).toHaveBeenCalledTimes(1)
      expect($api.staffs.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.staffs', async () => {
      expect(store.state.staffs.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.staffs.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingStaffs', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.staffs, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingStaffs.value).toBeFalse()

      const promise = store.getIndex({ all: true })

      expect(store.state.isLoadingStaffs.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingStaffs.value).toBeFalse()
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
