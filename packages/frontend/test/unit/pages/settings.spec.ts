/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import { SessionStore } from '~/composables/stores/create-session-store'
import { sessionStateKey } from '~/composables/stores/use-session-store'
import { SettingStore, settingStoreKey } from '~/composables/stores/use-setting-store'
import SettingsPage from '~/pages/settings.vue'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createSettingStoreStub } from '~~/stubs/create-setting-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/settings.vue', () => {
  const { mount } = setupComponentTest()
  const staff = createStaffStub()
  let sessionStore: SessionStore
  const settingStore: SettingStore = createSettingStoreStub()
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(SettingsPage, {
      ...options,
      ...provides([sessionStateKey, sessionStore.state]),
      ...provides([settingStoreKey, settingStore])
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
    jest.spyOn(sessionStore, 'get').mockResolvedValue()
    jest.spyOn(settingStore, 'get').mockResolvedValue()
    await mountComponent()
  })

  afterEach(() => {
    unmountComponent()
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should call settingStore.get', () => {
    expect(settingStore.get).toHaveBeenCalledTimes(1)
    expect(settingStore.get).toHaveBeenCalledWith()
  })
})
