/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import {
  useWithdrawalTransactionsStore,
  WithdrawalTransactionsStore
} from '~/composables/stores/use-withdrawal-transactions-store'
import { usePlugins } from '~/composables/use-plugins'
import {
  createWithdrawalTransactionIndexResponseStub
} from '~~/stubs/create-withdrawal-transaction-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-withdrawal-transactions-store', () => {
  const $api = createMockedApi('withdrawalTransactions')
  const plugins = createMockedPlugins({ $api })
  let store: WithdrawalTransactionsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useWithdrawalTransactionsStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('withdrawalTransactions', () => {
      it('should be ref to empty array', () => {
        expect(store.state.withdrawalTransactions).toBeRef()
        expect(store.state.withdrawalTransactions.value).toBeEmptyArray()
      })
    })

    describe('isLoadingWithdrawalTransactions', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingWithdrawalTransactions).toBeRef()
        expect(store.state.isLoadingWithdrawalTransactions.value).toBeFalse()
      })
    })

    describe('pagination', () => {
      it('should be ref to pagination object', () => {
        expect(store.state.pagination).toBeRef()
        expect(store.state.pagination.value).toStrictEqual({
          desc: true,
          page: 1,
          itemsPerPage: 10
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
    const response = createWithdrawalTransactionIndexResponseStub()
    const itemsPerPage = 10

    beforeEach(() => {
      jest.spyOn($api.withdrawalTransactions, 'getIndex').mockResolvedValue(response)
      store = useWithdrawalTransactionsStore()
    })

    afterEach(() => {
      mocked($api.withdrawalTransactions.getIndex).mockReset()
    })

    it('should call $api.withdrawalTransactions.getIndex', async () => {
      const params = { all: true, itemsPerPage }
      await store.getIndex(params)
      expect($api.withdrawalTransactions.getIndex).toHaveBeenCalledTimes(1)
      expect($api.withdrawalTransactions.getIndex).toHaveBeenCalledWith(params)
    })

    it('should update state.withdrawalTransactions', async () => {
      expect(store.state.withdrawalTransactions.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true, itemsPerPage })
      expect(store.state.withdrawalTransactions.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingWithdrawalTransactions', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.withdrawalTransactions, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingWithdrawalTransactions.value).toBeFalse()

      const promise = store.getIndex({ all: true, itemsPerPage })

      expect(store.state.isLoadingWithdrawalTransactions.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingWithdrawalTransactions.value).toBeFalse()
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
