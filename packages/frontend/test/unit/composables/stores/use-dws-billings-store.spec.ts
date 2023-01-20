/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers/index'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { DwsBillingsStore, useDwsBillingsStore } from '~/composables/stores/use-dws-billings-store'
import { usePlugins } from '~/composables/use-plugins'
import { createDwsBillingIndexResponseStub } from '~~/stubs/create-dws-billing-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-billings-store', () => {
  const $api = createMockedApi('dwsBillings')
  const plugins = createMockedPlugins({ $api })
  let store: DwsBillingsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsBillingsStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('dwsBillings', () => {
      it('should be ref to empty array', () => {
        expect(store.state.dwsBillings).toBeRef()
        expect(store.state.dwsBillings.value).toBeEmptyArray()
      })
    })

    describe('isLoadingDwsBillings', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingDwsBillings).toBeRef()
        expect(store.state.isLoadingDwsBillings.value).toBeFalse()
      })
    })

    describe('pagination', () => {
      it('should be ref to pagination object', () => {
        expect(store.state.pagination).toBeRef()
        expect(store.state.pagination.value).toStrictEqual({
          desc: false,
          page: 1,
          itemsPerPage: 10,
          sortBy: 'id'
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
    const response = createDwsBillingIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.dwsBillings, 'getIndex').mockResolvedValue(response)
      store = useDwsBillingsStore()
    })

    afterEach(() => {
      mocked($api.dwsBillings.getIndex).mockReset()
    })

    it('should call $api.dwsBillings.getIndex', async () => {
      await store.getIndex({ all: true })
      expect($api.dwsBillings.getIndex).toHaveBeenCalledTimes(1)
      expect($api.dwsBillings.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.dwsBillings', async () => {
      expect(store.state.dwsBillings.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.dwsBillings.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingDwsBillings', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.dwsBillings, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingDwsBillings.value).toBeFalse()

      const promise = store.getIndex({ all: true })

      expect(store.state.isLoadingDwsBillings.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingDwsBillings.value).toBeFalse()
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
