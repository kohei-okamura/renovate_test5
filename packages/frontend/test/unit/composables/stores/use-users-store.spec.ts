/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { UsersStore, useUsersStore } from '~/composables/stores/use-users-store'
import { usePlugins } from '~/composables/use-plugins'
import { createUserIndexResponseStub } from '~~/stubs/create-user-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-users-store', () => {
  const $api = createMockedApi('users')
  const plugins = createMockedPlugins({ $api })
  let store: UsersStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useUsersStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('users', () => {
      it('should be ref to empty array', () => {
        expect(store.state.users).toBeRef()
        expect(store.state.users.value).toBeEmptyArray()
      })
    })

    describe('isLoadingUsers', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingUsers).toBeRef()
        expect(store.state.isLoadingUsers.value).toBeFalse()
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
    const response = createUserIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.users, 'getIndex').mockResolvedValue(response)
      store = useUsersStore()
    })

    afterEach(() => {
      mocked($api.users.getIndex).mockReset()
    })

    it('should call $api.users.getIndex', async () => {
      await store.getIndex({ all: true })
      expect($api.users.getIndex).toHaveBeenCalledTimes(1)
      expect($api.users.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.users', async () => {
      expect(store.state.users.value).not.toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.users.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingUsers', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.users, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingUsers.value).toBeFalse()

      const promise = store.getIndex({ all: true })

      expect(store.state.isLoadingUsers.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingUsers.value).toBeFalse()
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
