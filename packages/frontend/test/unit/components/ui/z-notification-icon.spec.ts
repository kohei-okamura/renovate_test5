/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import ZNotificationIcon from '~/components/ui/z-notification-icon.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-notification-icon.vue', () => {
  const { mount } = setupComponentTest()

  it('should not show badge when did not pass props', () => {
    const wrapper = mount(ZNotificationIcon)
    expect(wrapper).toMatchSnapshot()
  })

  it('should not show badge when it does not have notification', () => {
    const propsData = { numberOfNotices: 0 }
    const wrapper = mount(ZNotificationIcon, { propsData })
    // display: none の有無で分かるためスナップショットに任せる（ついでに disabled も分かる）
    expect(wrapper).toMatchSnapshot()
  })

  it('should show badge when it has notification', () => {
    const propsData = { numberOfNotices: 3 }
    const wrapper = mount(ZNotificationIcon, { propsData })
    // display: none の有無で分かるためスナップショットに任せる
    expect(wrapper).toMatchSnapshot()
  })

  it('should emit "click" when button clicked', async () => {
    const mockFn = jest.fn()
    const wrapper = mount({
      data: () => ({ handleClick: mockFn }),
      template: '<z-notification-icon :number-of-notices="3" @click="handleClick" />',
      components: { 'z-notification-icon': ZNotificationIcon }
    })
    const buttonWrapper = wrapper.find('[data-button]')
    await buttonWrapper.trigger('click')
    expect(mockFn).toHaveBeenCalledTimes(1)
  })
})
