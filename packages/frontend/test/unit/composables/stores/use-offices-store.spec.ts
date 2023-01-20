/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { OfficesStore, useOfficesStore } from '~/composables/stores/use-offices-store'
import { usePlugins } from '~/composables/use-plugins'
import { createOfficeIndexResponseStub } from '~~/stubs/create-office-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-offices-store', () => {
  const $api = createMockedApi('offices')
  const plugins = createMockedPlugins({ $api })
  let store: OfficesStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useOfficesStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('offices', () => {
      it('should be ref to empty array', () => {
        expect(store.state.offices).toBeRef()
        expect(store.state.offices.value).toBeEmptyArray()
      })
    })

    describe('isLoadingOffices', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingOffices).toBeRef()
        expect(store.state.isLoadingOffices.value).toBeFalse()
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
    const response = createOfficeIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.offices, 'getIndex').mockResolvedValue(response)
      store = useOfficesStore()
    })

    afterEach(() => {
      mocked($api.offices.getIndex).mockReset()
    })

    it('should call $api.offices.getIndex', async () => {
      await store.getIndex({ all: true })
      expect($api.offices.getIndex).toHaveBeenCalledTimes(1)
      expect($api.offices.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.offices', async () => {
      expect(store.state.offices.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.offices.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingOffices', async () => {
      const deferred = new Deferred<typeof response>()
      mocked($api.offices.getIndex).mockReturnValue(deferred.promise)
      expect(store.state.isLoadingOffices.value).toBeFalse()

      const promise = store.getIndex({ all: true })

      expect(store.state.isLoadingOffices.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingOffices.value).toBeFalse()
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
