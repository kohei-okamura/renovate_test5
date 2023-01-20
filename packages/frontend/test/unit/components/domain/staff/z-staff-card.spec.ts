/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZStaffCard from '~/components/domain/staff/z-staff-card.vue'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { STAFF_ID_MIN } from '~~/stubs/create-staff-stub'
import { createUseOfficeGroupsStub } from '~~/stubs/create-use-office-groups-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-office-groups')

describe('z-staff-card.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue>) {
    wrapper = mount(ZStaffCard, options)
  }

  beforeAll(() => {
    mocked(useOfficeGroups).mockReturnValue(createUseOfficeGroupsStub())
  })

  afterAll(() => {
    mocked(useOfficeGroups).mockReset()
  })

  it('should be rendered correctly', () => {
    const { offices, roles, staff } = createStaffResponseStub(STAFF_ID_MIN)
    const propsData = {
      ...staff,
      offices,
      roles
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
  })

  it('should not display roles when hide-roles is true', () => {
    const { offices, roles, staff } = createStaffResponseStub(STAFF_ID_MIN + 1)
    const propsData = {
      ...staff,
      offices,
      roles,
      hideRoles: true
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
  })
})
