/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Slots, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZFabSpeedDialButton from '~/components/ui/z-fab-speed-dial-button.vue'
import { $icons } from '~/plugins/icons'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-fab-speed-dial.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const propsData = {
      icon: $icons.add
    }
    const slots: Slots = {
      default: '新規登録'
    }
    wrapper = mount(ZFabSpeedDialButton, { propsData, slots })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    jest.runOnlyPendingTimers()
    expect(wrapper).toMatchSnapshot()
  })

  it('should emit "click" event when the button clicked', async () => {
    await click(() => wrapper.find('[data-button]'))
    const emitted = wrapper.emitted('click')
    expect(emitted).toHaveLength(1)
  })
})
