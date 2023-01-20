/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { PermissionsStore, usePermissionsStore } from '~/composables/stores/use-permissions-store'
import { usePlugins } from '~/composables/use-plugins'
import { createPermissionIndexResponseStub } from '~~/stubs/create-permission-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-permissions-store', () => {
  const $api = createMockedApi('permissions')
  const plugins = createMockedPlugins({ $api })
  let store: PermissionsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = usePermissionsStore()
    })

    it('should have 2 values (2 states)', () => {
      expect(keys(store.state)).toHaveLength(2)
    })

    describe('permissionGroups', () => {
      it('should be ref to empty array', () => {
        expect(store.state.permissionGroups).toBeRef()
        expect(store.state.permissionGroups.value).toBeEmptyArray()
      })
    })

    describe('isLoadingPermissions', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingPermissions).toBeRef()
        expect(store.state.isLoadingPermissions.value).toBeFalse()
      })
    })
  })

  describe('getIndex', () => {
    const response = createPermissionIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.permissions, 'getIndex').mockResolvedValue(response)
      store = usePermissionsStore()
    })

    afterEach(() => {
      mocked($api.permissions.getIndex).mockReset()
    })

    it('should call $api.permissions.getIndex', async () => {
      await store.getIndex()
      expect($api.permissions.getIndex).toHaveBeenCalledTimes(1)
      expect($api.permissions.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.permissionGroups', async () => {
      expect(store.state.permissionGroups.value).not.toStrictEqual(response.list)
      await store.getIndex()
      expect(store.state.permissionGroups.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingPermissions', async () => {
      const deferred = new Deferred<typeof response>()
      mocked($api.permissions.getIndex).mockReturnValue(deferred.promise)
      expect(store.state.isLoadingPermissions.value).toBeFalse()

      const promise = store.getIndex()

      expect(store.state.isLoadingPermissions.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingPermissions.value).toBeFalse()
    })
  })
})
