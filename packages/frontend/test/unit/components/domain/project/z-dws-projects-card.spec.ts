/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZDwsProjectsCard from '~/components/domain/project/z-dws-projects-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createContractStubsForUser } from '~~/stubs/create-contract-stub'
import { createDwsProjectStubs } from '~~/stubs/create-dws-project-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-dws-projects-card.vue', () => {
  const { shallowMount } = setupComponentTest()
  const user = createUserStub()
  const contracts = createContractStubsForUser(user.id)
  const items = createDwsProjectStubs(contracts)
  const propsData = {
    contracts,
    items,
    user
  }
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    wrapper = shallowMount(ZDwsProjectsCard, {
      ...provides(
        [sessionStoreKey, createAuthStub({ isSystemAdmin: true })]
      ),
      propsData
    })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
