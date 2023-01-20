/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { RolesStore, useRolesStore } from '~/composables/stores/use-roles-store'
import { usePlugins } from '~/composables/use-plugins'
import { createRoleIndexResponseStub } from '~~/stubs/create-role-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-roles-store', () => {
  const $api = createMockedApi('roles')
  const plugins = createMockedPlugins({ $api })
  let store: RolesStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useRolesStore()
    })

    it('should have 2 values (2 states, 0 getter)', () => {
      expect(keys(store.state)).toHaveLength(2)
    })

    describe('roles', () => {
      it('should be ref to empty array', () => {
        expect(store.state.roles).toBeRef()
        expect(store.state.roles.value).toBeEmptyArray()
      })
    })

    describe('isLoadingRoles', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingRoles).toBeRef()
        expect(store.state.isLoadingRoles.value).toBeFalse()
      })
    })
  })

  describe('getIndex', () => {
    const response = createRoleIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.roles, 'getIndex').mockResolvedValue(response)
      store = useRolesStore()
    })

    afterEach(() => {
      mocked($api.roles.getIndex).mockReset()
    })

    it('should call $api.roles.getIndex', async () => {
      await store.getIndex()
      expect($api.roles.getIndex).toHaveBeenCalledTimes(1)
      expect($api.roles.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.roles', async () => {
      expect(store.state.roles.value).not.toStrictEqual(response.list)
      await store.getIndex()
      expect(store.state.roles.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingRoles', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.roles, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingRoles.value).toBeFalse()

      const promise = store.getIndex()

      expect(store.state.isLoadingRoles.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingRoles.value).toBeFalse()
    })
  })
})
