/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { RoleStore, useRoleStore } from '~/composables/stores/use-role-store'
import { usePlugins } from '~/composables/use-plugins'
import { createRoleResponseStub } from '~~/stubs/create-role-response-stub'
import { createRoleStub } from '~~/stubs/create-role-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-role-store', () => {
  const $api = createMockedApi('roles')
  const plugins = createMockedPlugins({ $api })
  let store: RoleStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useRoleStore()
    })

    it('should have a value', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('role', () => {
      it('should be ref to undefined', () => {
        expect(store.state.role).toBeRef()
        expect(store.state.role.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createRoleResponseStub()

    beforeEach(() => {
      jest.spyOn($api.roles, 'get').mockResolvedValue(response)
      store = useRoleStore()
    })

    afterEach(() => {
      mocked($api.roles.get).mockReset()
    })

    it('should call $api.roles.get', async () => {
      await store.get({ id })
      expect($api.roles.get).toHaveBeenCalledTimes(1)
      expect($api.roles.get).toHaveBeenCalledWith({ id })
    })

    it('should update state.role', async () => {
      expect(store.state.role.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.role.value).toStrictEqual(response.role)
    })
  })

  describe('update', () => {
    const id = 1
    const current = createRoleResponseStub()
    const role = createRoleStub(current.role.id)
    const updated = { role }
    const form = {
      name: role.name,
      isSystemAdmin: role.isSystemAdmin,
      permissions: Object.fromEntries(role.permissions.map(x => [x, true])),
      scope: role.scope
    }

    beforeAll(() => {
      store = useRoleStore()
      jest.spyOn($api.roles, 'get').mockResolvedValue(current)
      jest.spyOn($api.roles, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id })
    })

    it('should call $api.roles.update', async () => {
      await store.update({ form, id })
      expect($api.roles.update).toHaveBeenCalledTimes(1)
      expect($api.roles.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.role', async () => {
      expect(store.state.role.value).toStrictEqual(current.role)
      await store.update({ form, id })
      expect(store.state.role.value).toStrictEqual(updated.role)
    })
  })
})
