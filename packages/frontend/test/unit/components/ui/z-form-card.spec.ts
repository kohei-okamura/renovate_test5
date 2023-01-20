/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZFormCard from '~/components/ui/z-form-card.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-form-card.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const propsData = {
      title: '5th DIMENSION'
    }
    wrapper = mount(ZFormCard, { propsData })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
