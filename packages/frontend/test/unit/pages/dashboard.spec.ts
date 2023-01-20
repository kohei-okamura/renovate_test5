/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Stubs, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import DashboardPage from '~/pages/dashboard.vue'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/dashboard.vue', () => {
  const { mount } = setupComponentTest()
  const stubs: Stubs = {
    'z-my-shifts-card': true
  }
  let wrapper: Wrapper<Vue>

  function mountComponent (auth: Partial<Auth> = { isSystemAdmin: true }) {
    wrapper = mount(DashboardPage, {
      ...provides([sessionStoreKey, createAuthStub(auth)]),
      stubs
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

  describe('schedule', () => {
    const requiredPermissions: Permission[] = [Permission.listShifts]
    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('z-my-shifts-card-stub')
      unmountComponent()
    })

    it(`should be rendered when the staff has permissions: ${requiredPermissions}`, () => {
      const auth = {
        permissions: requiredPermissions
      }

      mountComponent(auth)
      expect(wrapper).toContainElement('z-my-shifts-card-stub')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }

      mountComponent(auth)
      expect(wrapper).not.toContainElement('z-my-shifts-card-stub')
      unmountComponent()
    })
  })
})
