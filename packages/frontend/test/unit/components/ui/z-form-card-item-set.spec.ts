/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Slots } from '@vue/test-utils'
import ZDataCardItem from '~/components/ui/z-data-card-item.vue'
import ZFormCardItemSet from '~/components/ui/z-form-card-item-set.vue'
import { $icons } from '~/plugins/icons'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-form-card-item-set.vue', () => {
  const { mount } = setupComponentTest()

  it('should be rendered correctly', () => {
    const propsData = {
      icon: $icons.user
    }
    const slots: Slots = {
      default: 'BATTLE AND ROMANCE'
    }
    const wrapper = mount(ZFormCardItemSet, { propsData, slots })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly without icon', () => {
    const slots: Slots = {
      default: 'LADY MAY'
    }
    const wrapper = mount(ZDataCardItem, { slots })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly without slots', () => {
    const propsData = {
      icon: $icons.staff,
      value: 'THE SHOW'
    }
    const wrapper = mount(ZDataCardItem, { propsData })
    expect(wrapper).toMatchSnapshot()
  })
})
