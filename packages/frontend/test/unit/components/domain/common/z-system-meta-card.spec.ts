/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZSystemMetaCard from '~/components/domain/common/z-system-meta-card.vue'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-system-meta-card.vue', () => {
  const { mount } = setupComponentTest()
  const staff = createStaffStub()
  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue>) {
    wrapper = mount(ZSystemMetaCard, options)
  }

  it('should be rendered correctly', () => {
    const propsData = {
      id: staff.id,
      createdAt: staff.createdAt,
      updatedAt: staff.updatedAt
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly without createdAt', () => {
    const propsData = {
      id: staff.id,
      updatedAt: staff.updatedAt
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly without updatedAt', () => {
    const propsData = {
      id: staff.id,
      createdAt: staff.createdAt
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly with id only', () => {
    const propsData = {
      id: staff.id
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
  })
})
