/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZFormActionButton from '~/components/ui/z-form-action-button.vue'
import { $icons } from '~/plugins/icons'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-form-action-button.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const propsData = {
      icon: $icons.save,
      text: '保存'
    }
    wrapper = mount(ZFormActionButton, { propsData })
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
