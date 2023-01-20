/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZSubheader from '~/components/ui/z-subheader.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-subheader.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  it('should be rendered correctly', () => {
    const context = {
      staticClass: 'blue'
    }
    const slots = {
      default: 'This is subtitle'
    }
    wrapper = mount(ZSubheader, { context, slots })
    expect(wrapper).toMatchSnapshot()
    wrapper.destroy()
  })
})
