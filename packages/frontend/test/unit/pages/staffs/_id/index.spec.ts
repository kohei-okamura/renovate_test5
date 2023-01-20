/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { staffStateKey } from '~/composables/stores/use-staff-store'
import { Auth } from '~/models/auth'
import StaffsViewPage from '~/pages/staffs/_id/index.vue'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { createStaffStoreStub } from '~~/stubs/create-staff-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/staffs/_id/index.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue & any>

  async function mountComponent (
    options: MountOptions<Vue> = {},
    auth: Partial<Auth> = { isSystemAdmin: true }
  ) {
    const response = createStaffResponseStub()
    const store = createStaffStoreStub(response)
    wrapper = mount(StaffsViewPage, {
      ...options,
      ...provides(
        [sessionStoreKey, createAuthStub(auth)],
        [staffStateKey, store.state])
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [
      Permission.updateStaffs
    ]

    afterEach(() => {
      unmountComponent()
    })

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
    })

    it('should be rendered when the staff has permissions', () => {
      mountComponent({}, { permissions: requiredPermissions })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-fab]')
    })
  })
})
