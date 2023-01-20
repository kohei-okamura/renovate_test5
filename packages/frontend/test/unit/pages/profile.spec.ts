/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import { SessionStore } from '~/composables/stores/create-session-store'
import { sessionStateKey } from '~/composables/stores/use-session-store'
import { StaffStore } from '~/composables/stores/use-staff-store'
import ProfilePage from '~/pages/profile.vue'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createStaffStoreStub } from '~~/stubs/create-staff-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/profile.vue', () => {
  const { mount } = setupComponentTest()
  const staff = createStaffStub()
  let sessionStore: SessionStore
  let staffStore: StaffStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ProfilePage, {
      ...options,
      ...provides([sessionStateKey, sessionStore.state])
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(async () => {
    sessionStore = createSessionStoreStub({
      auth: {
        isSystemAdmin: true,
        permissions: [Permission.listInternalOffices],
        staff
      }
    })
    staffStore = createStaffStoreStub()
    jest.spyOn(sessionStore, 'get').mockResolvedValue()
    jest.spyOn(staffStore, 'get').mockResolvedValue()
    await mountComponent()
  })

  afterEach(() => {
    unmountComponent()
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should call staffStore.get', () => {
    expect(staffStore.get).toHaveBeenCalledTimes(1)
    expect(staffStore.get).toHaveBeenCalledWith({ id: staff.id })
  })
})
