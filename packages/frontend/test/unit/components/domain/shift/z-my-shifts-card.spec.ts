/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import flushPromises from 'flush-promises'
import Vue from 'vue'
import ZMyShiftsCard from '~/components/domain/shift/z-my-shifts-card.vue'
import { sessionStateKey } from '~/composables/stores/use-session-store'
import { Shift } from '~/models/shift'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createShiftStubs } from '~~/stubs/create-shift-stub'
import { createShiftsStoreStub } from '~~/stubs/create-shifts-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-my-shifts-card.vue', () => {
  const { shallowMount } = setupComponentTest()
  const staff = createStaffStub()
  const sessionStore = createSessionStoreStub({
    auth: {
      isSystemAdmin: true,
      permissions: [Permission.listInternalOffices],
      staff
    }
  })

  let wrapper: Wrapper<Vue>

  async function mountComponent (shifts: Shift[] = createShiftStubs(14)) {
    createShiftsStoreStub({ shifts })
    wrapper = shallowMount(ZMyShiftsCard, {
      ...provides([sessionStateKey, sessionStore.state])
    })
    await flushPromises()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should display message of no shifts when no data', async () => {
    await mountComponent([])
    expect(wrapper.find('.z-my-shifts-card_no-data').text()).toBe('勤務シフトはありません。')
    unmountComponent()
  })
})
