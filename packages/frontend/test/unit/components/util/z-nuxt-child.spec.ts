/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZNuxtChild from '~/components/util/z-nuxt-child.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-nuxt-child.vue', () => {
  const { shallowMount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    wrapper = shallowMount(
      ZNuxtChild,
      { stubs: ['z-progress'] }
    )
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should display progress if not yet resolved (props.resolved is false)', async () => {
    await wrapper.setProps({ resolved: false })
    expect(wrapper.findComponent({ ref: 'progress' })).toExist()
  })

  it('should not display progress if already resolved (props.resolved is true)', async () => {
    await wrapper.setProps({ resolved: true })
    expect(wrapper.findComponent({ ref: 'progress' })).not.toExist()
  })
})
