/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZUserSummary from '~/components/domain/user/z-user-summary.vue'
import { createUserStub } from '~~/stubs/create-user-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-user-summary.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const user = createUserStub()
    const propsData = { user }
    wrapper = mount(ZUserSummary, { propsData })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  // FIXME: なぜかスナップショットがちゃんと生成されない. 後日調査の上対応する.
  it.skip('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
