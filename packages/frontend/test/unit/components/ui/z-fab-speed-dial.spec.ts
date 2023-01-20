/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Stubs, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import { VOverlay } from 'vuetify/lib'
import ZFabSpeedDial from '~/components/ui/z-fab-speed-dial.vue'
import { $icons } from '~/plugins/icons'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-fab-speed-dial.vue', () => {
  const { mount } = setupComponentTest()
  const propsData = {
    icon: $icons.add
  }
  const slots = {
    default: [
      '<div>A</div>',
      '<div>B</div>',
      '<div>C</div>'
    ]
  }
  const stubs: Stubs = {
    'v-overlay': VOverlay
  }
  let wrapper: Wrapper<Vue>

  beforeEach(() => {
    wrapper = mount(ZFabSpeedDial, { propsData, slots, stubs })
  })

  afterEach(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should show buttons when the fab clicked', async () => {
    await click(() => wrapper.find('[data-z-fab-speed-dial-fab]'))
    expect(wrapper).toMatchSnapshot()
  })

  it('should show overlay when the fab clicked', async () => {
    const overlay = wrapper.find('[data-z-fab-speed-dial-overlay]')
    expect(overlay.props('value')).toBeFalse()
    await click(() => wrapper.find('[data-z-fab-speed-dial-fab]'))
    expect(overlay.props('value')).toBeTrue()
  })
})
