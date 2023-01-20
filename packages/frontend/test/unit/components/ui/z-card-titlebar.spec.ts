/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZCardTitlebar from '~/components/ui/z-card-titlebar.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-card-titlebar.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const propsData = {
      color: 'primary'
    }
    const slots = {
      default: 'タイトル'
    }
    wrapper = mount(ZCardTitlebar, { propsData, slots })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
