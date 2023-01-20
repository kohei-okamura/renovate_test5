/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { rolesStoreKey } from '~/composables/stores/use-roles-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import RolesIndexPage from '~/pages/roles/index.vue'
import { Plugins } from '~/plugins'
import { createRoleStubs } from '~~/stubs/create-role-stub'
import { createRolesStoreStub } from '~~/stubs/create-roles-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/roles/index.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('roles')
  const mocks: Partial<Plugins> = {
    $api
  }
  const roles = createRoleStubs()
  const rolesStore = createRolesStoreStub({ roles })

  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue> = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    wrapper = mount(RolesIndexPage, {
      ...options,
      ...provides(
        [rolesStoreKey, rolesStore],
        [sessionStoreKey, createAuthStub(auth)]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    mocked(rolesStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should dispatch offices/getIndex', () => {
    mountComponent()

    expect(rolesStore.getIndex).toHaveBeenCalledTimes(1)
    expect(rolesStore.getIndex).toHaveBeenCalledWith()

    unmountComponent()
  })

  describe('FAB', () => {
    const requiredPermissions: Permission[] = [Permission.createRoles]

    afterEach(() => {
      unmountComponent()
    })

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
    })

    it(`should be rendered when the staff has permissions: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement('[data-fab]')
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-fab]')
    })
  })
})
