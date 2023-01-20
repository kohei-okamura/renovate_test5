/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZPageDialog from '~/components/ui/z-page-dialog.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-page-dialog.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeEach(() => {
    const propsData = {
      value: true
    }
    const slots = {
      default: '<div class="slot-stub" id="default" />'
    }
    wrapper = mount(ZPageDialog, { propsData, slots })
  })

  afterEach(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should open after 100ms', () => {
    jest.advanceTimersByTime(100)
    expect(wrapper).toMatchSnapshot()
  })

  it('should close when the falsy value given', () => {
    expect(wrapper).toMatchSnapshot()
    wrapper.setProps({ value: false })
    expect(wrapper).toMatchSnapshot()
  })
})
