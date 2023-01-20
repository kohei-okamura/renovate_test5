/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { UserBillingsStore, useUserBillingsStore } from '~/composables/stores/use-user-billings-store'
import { usePlugins } from '~/composables/use-plugins'
import { createUserBillingIndexResponseStub } from '~~/stubs/create-user-billing-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-user-billings-store', () => {
  const $api = createMockedApi('userBillings')
  const plugins = createMockedPlugins({ $api })
  let store: UserBillingsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useUserBillingsStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('userBillings', () => {
      it('should be ref to empty array', () => {
        expect(store.state.userBillings).toBeRef()
        expect(store.state.userBillings.value).toBeEmptyArray()
      })
    })

    describe('isLoadingUserBillings', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingUserBillings).toBeRef()
        expect(store.state.isLoadingUserBillings.value).toBeFalse()
      })
    })

    describe('pagination', () => {
      it('should be ref to pagination object', () => {
        expect(store.state.pagination).toBeRef()
        expect(store.state.pagination.value).toStrictEqual({
          count: 0,
          page: 1,
          itemsPerPage: 1000
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
    const response = createUserBillingIndexResponseStub()
    const itemsPerPage = 10

    beforeEach(() => {
      jest.spyOn($api.userBillings, 'getIndex').mockResolvedValue(response)
      store = useUserBillingsStore()
    })

    afterEach(() => {
      mocked($api.userBillings.getIndex).mockReset()
    })

    it('should call $api.userBillings.getIndex', async () => {
      const params = { all: true, itemsPerPage }
      await store.getIndex(params)
      expect($api.userBillings.getIndex).toHaveBeenCalledTimes(1)
      expect($api.userBillings.getIndex).toHaveBeenCalledWith(params)
    })

    it('should update state.userBillings', async () => {
      expect(store.state.userBillings.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true, itemsPerPage })
      expect(store.state.userBillings.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingUserBillings', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.userBillings, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingUserBillings.value).toBeFalse()

      const promise = store.getIndex({ all: true, itemsPerPage })

      expect(store.state.isLoadingUserBillings.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingUserBillings.value).toBeFalse()
    })

    it('should update state.pagination', async () => {
      expect(store.state.pagination.value).not.toStrictEqual(response.pagination)
      await store.getIndex({ all: true, itemsPerPage })
      expect(store.state.pagination.value).toStrictEqual(response.pagination)
    })

    it('should update state.queryParams', async () => {
      const params = { all: true, itemsPerPage }
      expect(store.state.queryParams.value).not.toStrictEqual(params)
      await store.getIndex(params)
      expect(store.state.queryParams.value).toStrictEqual(params)
    })
  })
})
