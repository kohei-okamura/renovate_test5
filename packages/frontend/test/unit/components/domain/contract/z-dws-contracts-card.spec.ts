/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZDwsContractsCard from '~/components/domain/contract/z-dws-contracts-card.vue'
import { createContractStubsForUser } from '~~/stubs/create-contract-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-dws-contracts-card.vue', () => {
  const { shallowMount } = setupComponentTest()
  const user = createUserStub()
  const items = createContractStubsForUser(user.id)
  const propsData = {
    items,
    user
  }
  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = shallowMount(ZDwsContractsCard, { propsData })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })
})
