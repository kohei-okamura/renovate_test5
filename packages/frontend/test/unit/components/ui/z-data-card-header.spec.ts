/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Slots, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZDataCardHeader from '~/components/ui/z-data-card-header.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-data-card-header.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const slots: Slots = {
      default: '<div data-slot-default></div>'
    }
    wrapper = mount(ZDataCardHeader, { slots })
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
