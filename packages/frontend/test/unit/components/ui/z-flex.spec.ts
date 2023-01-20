/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZFlex from '~/components/ui/z-flex.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-flex.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const slots = {
      default: '<div>FOO BAR BAZ</div>'
    }
    wrapper = mount(ZFlex, { slots })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
