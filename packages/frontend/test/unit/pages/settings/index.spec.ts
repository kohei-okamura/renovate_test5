/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import { permissionsStoreKey } from '~/composables/stores/use-permissions-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { SettingData, settingStateKey } from '~/composables/stores/use-setting-store'
import { Auth } from '~/models/auth'
import SettingViewPage from '~/pages/settings/index.vue'
import { Plugins } from '~/plugins'
import { createPermissionGroupStubs } from '~~/stubs/create-permission-group-stub'
import { createPermissionsStoreStub } from '~~/stubs/create-permissions-store-stub'
import { createSettingStoreStub } from '~~/stubs/create-setting-store-stub'
import { createSettingResponseStub, createSettingStub } from '~~/stubs/create-setting-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/settings/index.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('setting')
  const mocks: Partial<Plugins> = {
    $api
  }
  const stub = createSettingResponseStub()
  let wrapper: Wrapper<Vue & any>

  function mountComponent (
    auth: Partial<Auth> = { isSystemAdmin: true },
    data: Partial<SettingData> = {}
  ) {
    const permissionGroups = createPermissionGroupStubs()
    const permissionsStore = createPermissionsStoreStub({ permissionGroups })
    const settingStore = createSettingStoreStub({
      ...stub,
      ...data
    })
    wrapper = mount(SettingViewPage, {
      ...provides(
        [permissionsStoreKey, permissionsStore],
        [settingStateKey, settingStore.state],
        [sessionStoreKey, createAuthStub(auth)]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('No register bankingClientCode alert', () => {
    const requiredPermissions: Permission[] = [Permission.createOrganizationSettings]
    const selector = '[data-no-banking-client-code-alert]'
    const emptyBankingClientCodeStub = {
      organizationSetting: {
        ...createSettingStub(),
        bankingClientCode: ''
      }
    }

    afterEach(() => {
      unmountComponent()
    })

    it('should be rendered when the setting has permission', () => {
      mountComponent({ permissions: requiredPermissions }, { ...emptyBankingClientCodeStub })
      expect(wrapper).toContainElement(selector)
      expect(wrapper.find(selector)).toMatchSnapshot()
    })

    it(`should not be rendered when the setting does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ permissions })
      expect(wrapper).not.toContainElement(selector)
    })
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [Permission.updateOrganizationSettings]
    const selector = '[data-fab]'

    afterEach(() => {
      unmountComponent()
    })

    it('should be rendered when setting auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement(selector)
      expect(wrapper.find(selector)).toMatchSnapshot()
    })

    it('should be rendered when the setting has permission', () => {
      mountComponent({ permissions: requiredPermissions })
      expect(wrapper).toContainElement(selector)
      expect(wrapper.find(selector)).toMatchSnapshot()
    })

    it(`should not be rendered when the setting does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ permissions })
      expect(wrapper).not.toContainElement(selector)
    })
  })
})
