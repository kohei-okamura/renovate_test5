/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZUserCard from '~/components/domain/user/z-user-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createUserStub, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-user-card.vue', () => {
  const { mount } = setupComponentTest()
  const user = createUserStub(USER_ID_MIN)
  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue>) {
    wrapper = mount(ZUserCard, {
      ...provides([sessionStoreKey, createAuthStub({ isSystemAdmin: true })]),
      ...options
    })
  }

  it('should be rendered correctly', () => {
    mountComponent({ propsData: { user } })
    expect(wrapper).toMatchSnapshot()
  })

  it('should not be rendered user link if have not permission', () => {
    mountComponent({
      propsData: { user },
      ...provides([sessionStoreKey, createAuthStub({})])
    })
    expect(wrapper).toMatchSnapshot()
  })
})
