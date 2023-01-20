/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers/index'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { LtcsBillingsStore, useLtcsBillingsStore } from '~/composables/stores/use-ltcs-billings-store'
import { usePlugins } from '~/composables/use-plugins'
import { createLtcsBillingIndexResponseStub } from '~~/stubs/create-ltcs-billing-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-billings-store', () => {
  const $api = createMockedApi('ltcsBillings')
  const plugins = createMockedPlugins({ $api })
  let store: LtcsBillingsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsBillingsStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('ltcsBillings', () => {
      it('should be ref to empty array', () => {
        expect(store.state.ltcsBillings).toBeRef()
        expect(store.state.ltcsBillings.value).toBeEmptyArray()
      })
    })

    describe('isLoadingLtcsBillings', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingLtcsBillings).toBeRef()
        expect(store.state.isLoadingLtcsBillings.value).toBeFalse()
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
    const response = createLtcsBillingIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.ltcsBillings, 'getIndex').mockResolvedValue(response)
      store = useLtcsBillingsStore()
    })

    afterEach(() => {
      mocked($api.ltcsBillings.getIndex).mockReset()
    })

    it('should call $api.ltcsBillings.getIndex', async () => {
      await store.getIndex({ all: true })
      expect($api.ltcsBillings.getIndex).toHaveBeenCalledTimes(1)
      expect($api.ltcsBillings.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.ltcsBillings', async () => {
      expect(store.state.ltcsBillings.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.ltcsBillings.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingLtcsBillings', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.ltcsBillings, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingLtcsBillings.value).toBeFalse()

      const promise = store.getIndex({ all: true })

      expect(store.state.isLoadingLtcsBillings.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingLtcsBillings.value).toBeFalse()
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
