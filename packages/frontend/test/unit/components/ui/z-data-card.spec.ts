/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Slots } from '@vue/test-utils'
import { FunctionalComponentOptions } from 'vue'
import ZDataCard from '~/components/ui/z-data-card.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-data-card.vue', () => {
  const { mount } = setupComponentTest()
  const propsData = {
    title: '5th DIMENSION'
  }
  const slots: Slots = {
    actions: '<div data-slot-actions></div>',
    default: '<div data-slot-default></div>'
  }

  it('should be rendered correctly', () => {
    const wrapper = mount(ZDataCard, { propsData, slots })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly with class', () => {
    const component: FunctionalComponentOptions = {
      name: 'ZDataCardTestWrapper',
      functional: true,
      render (h, { data }) {
        return h(ZDataCard, {
          ...data,
          class: 'test',
          props: propsData
        })
      }
    }
    const wrapper = mount(component)
    expect(wrapper).toMatchSnapshot()
  })
})
