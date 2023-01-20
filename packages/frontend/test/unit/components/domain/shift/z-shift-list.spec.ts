/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZShiftList from '~/components/domain/shift/z-shift-list.vue'
import { useUsers } from '~/composables/use-users'
import { createShiftStubs } from '~~/stubs/create-shift-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-users')

describe('z-shift-list.vue', () => {
  const { mount } = setupComponentTest()
  const propsData = {
    value: createShiftStubs(14)
  }

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ZShiftList, { propsData })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useUsers).mockReturnValue(createUseUsersStub())
  })

  afterAll(() => {
    mocked(useUsers).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })
})
