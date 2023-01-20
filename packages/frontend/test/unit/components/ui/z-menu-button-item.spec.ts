/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import ZMenuButtonItem from '~/components/ui/z-menu-button-item.vue'
import { $icons } from '~/plugins/icons'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-menu-button-item.vue', () => {
  const { mount } = setupComponentTest()

  it('should be rendered correctly', () => {
    const propsData = {
      icon: $icons.email,
      to: '/path/to'
    }
    const wrapper = mount(ZMenuButtonItem, { propsData })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly without icon', () => {
    const propsData = {
      to: '/path/to/awesome/page'
    }
    const wrapper = mount(ZMenuButtonItem, { propsData })
    expect(wrapper).toMatchSnapshot()
  })
})
