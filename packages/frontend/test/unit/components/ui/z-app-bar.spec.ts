/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import ZAppBar from '~/components/ui/z-app-bar.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import * as UseMatchMedia from '~/composables/use-match-media'
import { useNotificationApi } from '~/composables/use-notification-api'
import { createDrawerService } from '~/services/drawer-service'
import { createTabService, TabService } from '~/services/tab-service'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { provides } from '~~/test/helpers/provides'
import { resizeWindow } from '~~/test/helpers/resize-window'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-notification-api')

describe('z-app-bar.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const sessionStore = createSessionStoreStub({
    auth: {
      isSystemAdmin: true,
      permissions: [Permission.listInternalOffices],
      staff: createStaffStub()
    }
  })
  const $tabs = createMock<TabService>({
    ...createTabService()
  })
  const mocks = { $tabs }
  const provide = provides([sessionStoreKey, sessionStore])
  let spy: jest.SpyInstance

  const setSpy = (hasCoarsePointer: boolean) => {
    const value = { hasCoarsePointer: () => hasCoarsePointer }
    spy = jest.spyOn(UseMatchMedia, 'useMatchMedia').mockReturnValue(value)
  }

  beforeAll(() => {
    mocked(useNotificationApi).mockReturnValue({
      askPermission: () => Promise.resolve(),
      closeNotification: noop,
      isAlreadyConfirmed: computed(() => true),
      isDenied: computed(() => false),
      isGranted: computed(() => true),
      statusText: computed(() => 'デスクトップ通知は有効です'),
      sendNotification: noop
    })
  })

  afterAll(() => {
    mocked(useNotificationApi).mockReset()
  })

  afterEach(() => {
    spy.mockRestore()
  })

  it('should be rendered correctly', () => {
    setSpy(true)
    const wrapper = mount(ZAppBar, { mocks, ...provide })
    expect(wrapper).toMatchSnapshot()
    wrapper.destroy()
  })

  it('should be rendered correctly when the staff is not an administrator', () => {
    const sessionStore = createSessionStoreStub({
      auth: {
        isSystemAdmin: false,
        permissions: [Permission.listInternalOffices],
        staff: createStaffStub()
      }
    })
    const provide = provides([sessionStoreKey, sessionStore])
    setSpy(true)
    const wrapper = mount(ZAppBar, { mocks, ...provide })
    expect(wrapper).toMatchSnapshot()
    wrapper.destroy()
  })

  it.each`
    label                   | expected | tabs
    ${'does not have tabs'} | ${'32'}  | ${[]}
    ${'has tabs'}           | ${'80'}  | ${[{ label: 'Home', to: '/home' }]}
  `('should "extensionHeight" is $expected when $label', ({ expected, tabs }) => {
    setSpy(true)
    $tabs.update(tabs)
    const wrapper = shallowMount(ZAppBar, { mocks, ...provide })

    expect(wrapper.attributes().extensionheight).toStrictEqual(expected)

    wrapper.destroy()
  })

  describe('initial display of wide screen', () => {
    it('should not show menu button when device does not have coarse pointer', () => {
      setSpy(false)

      const wrapper = shallowMount(ZAppBar, { mocks })

      expect(wrapper.find('[data-menu-button]')).not.toExist()

      wrapper.destroy()
    })

    it('should show menu button when device has coarse pointer', () => {
      setSpy(true)

      const wrapper = shallowMount(ZAppBar, { mocks })

      expect(wrapper.find('[data-menu-button]')).toExist()

      wrapper.destroy()
    })
  })

  describe('initial display of narrow screen', () => {
    it('should show menu button when device does not have coarse pointer', async () => {
      setSpy(false)

      const wrapper = shallowMount(ZAppBar, { mocks })
      const width = wrapper.vm.$vuetify.breakpoint.thresholds.sm - 1

      await resizeWindow({ width }, () => {
        expect(wrapper.find('[data-menu-button]')).toExist()
        wrapper.destroy()
      })
    })

    it('should show menu button when device has coarse pointer', async () => {
      setSpy(true)

      const wrapper = shallowMount(ZAppBar, { mocks })
      const width = wrapper.vm.$vuetify.breakpoint.thresholds.sm - 1

      await resizeWindow({ width }, () => {
        expect(wrapper.find('[data-menu-button]')).toExist()
        wrapper.destroy()
      })
    })
  })

  it('should call `$drawer.set()` when click menu button', async () => {
    setSpy(false)

    const $drawer = createDrawerService()
    const spy = jest.spyOn($drawer, 'set')

    const localMocks = { ...mocks, ...{ $drawer } }
    const wrapper = mount(ZAppBar, { mocks: localMocks, ...provide })
    await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.xs }, async () => {
      expect($drawer.set).not.toHaveBeenCalled()
      await click(() => wrapper.find('[data-menu-button]'))
      expect(spy).toHaveBeenCalledTimes(1)
      expect(spy).toHaveBeenCalledWith(true)
    })

    wrapper.destroy()
  })

  describe('notification', () => {
    it('should not show badge when it does not have notifications', () => {
      setSpy(true)

      const propsData = { numberOfNotices: 0 }
      const wrapper = mount(ZAppBar, { mocks, ...provide, propsData })
      // display: none の有無で分かるためスナップショットに任せる
      expect(wrapper.find('[data-notification-icon]')).toMatchSnapshot()

      wrapper.destroy()
    })

    it('should show badge when it has notifications', () => {
      setSpy(true)

      const propsData = { numberOfNotices: 3 }
      const wrapper = mount(ZAppBar, { mocks, ...provide, propsData })
      // display: none の有無で分かるためスナップショットに任せる
      expect(wrapper.find('[data-notification-icon]')).toMatchSnapshot()

      wrapper.destroy()
    })

    it('should emit "click:bell" when icon emitted "click"', async () => {
      setSpy(true)

      const mockFn = jest.fn()
      const wrapper = mount({
        data: () => ({ handleClick: mockFn }),
        template: '<z-app-bar :number-of-notices="3" @click:bell="handleClick" />',
        components: { 'z-app-bar': ZAppBar }
      }, { mocks, ...provide })

      await wrapper.find('[data-notification-icon]').vm.$emit('click')

      expect(mockFn).toHaveBeenCalledTimes(1)

      wrapper.destroy()
    })
  })
})
