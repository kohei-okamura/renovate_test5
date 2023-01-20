/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { nextTick } from '@nuxtjs/composition-api'
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { sessionStateKey } from '~/composables/stores/use-session-store'
import { useUsers } from '~/composables/use-users'
import { NuxtContext } from '~/models/nuxt'
import CallingsViewPage from '~/pages/callings/_token.vue'
import { $datetime } from '~/services/datetime-service'
import { createCallingIndexResponseStub } from '~~/stubs/create-callings-index-response-stub'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-users')

describe('pages/calling/_token.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('callings')
  const $route = createMockedRoute({
    params: {
      token: 'x'.repeat(60)
    }
  })
  const mocks = {
    $api,
    $route
  }
  const staff = createStaffStub()
  const sessionStore = createSessionStoreStub({
    auth: {
      isSystemAdmin: true,
      permissions: [Permission.listInternalOffices],
      staff
    }
  })
  const callingStubs = createCallingIndexResponseStub()

  let wrapper: Wrapper<Vue>

  async function mountComponent () {
    wrapper = mount(CallingsViewPage, () => ({
      ...provides([sessionStateKey, sessionStore.state]),
      mocks
    }))
    await nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useUsers).mockReturnValue(createUseUsersStub())
    jest.spyOn($api.callings, 'acknowledge').mockResolvedValue()
    jest.spyOn($api.callings, 'getIndex').mockResolvedValue(callingStubs)
  })

  afterAll(() => {
    mocked($api.callings.acknowledge).mockReset()
    mocked($api.callings.getIndex).mockReset()
    mocked(useUsers).mockReset()
  })

  beforeEach(() => {
    mocked($api.callings.getIndex).mockClear()
  })

  it('should be rendered correctly', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('v-alert rendering', () => {
    it('should be rendered v-alert type of error,when start time of schedule is within one hour from the current time', async () => {
      await mountComponent()
      const temp: any = callingStubs
      temp.list[0].schedule.start = $datetime.now
      await wrapper.vm.$nextTick()
      expect(wrapper.find('div[role = alert]').classes()).toContain('error')
      unmountComponent()
    })

    it('should be rendered v-alert type of error,when start time of schedule is not within one hour from the current time', async () => {
      await mountComponent()
      const temp: any = callingStubs
      temp.list[0].schedule.start = $datetime.now.plus({ hours: 1 })
      await wrapper.vm.$nextTick()
      expect(wrapper.find('div[role = alert]').classes()).toContain('success')
      unmountComponent()
    })
  })

  it('should display message of no shifts when no data', async () => {
    await mountComponent()
    callingStubs.list = Array(0)
    jest.spyOn($api.callings, 'getIndex').mockResolvedValueOnce(callingStubs)
    await wrapper.vm.$nextTick()
    expect(wrapper.find('.z-my-shifts-card_no-data').text()).toBe('勤務シフトはありません。')
    unmountComponent()
  })

  describe('setup', () => {
    it('should called api', async () => {
      await mountComponent()
      expect($api.callings.getIndex).toHaveBeenCalledTimes(1)
      unmountComponent()
    })
  })

  describe('validate', () => {
    beforeAll(async () => {
      await mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should return true when valid token given', () => {
      const params = { token: 'x'.repeat(60) }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when low token value', () => {
      const params = { token: 'x'.repeat(30) }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when valid token not given', () => {
      const params = {}
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
