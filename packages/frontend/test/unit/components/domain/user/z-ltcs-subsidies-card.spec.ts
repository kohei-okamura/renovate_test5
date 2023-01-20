/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZLtcsSubsidiesCard from '~/components/domain/user/z-ltcs-subsidies-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createLtcsSubsidyStubsForUser } from '~~/stubs/create-ltcs-subsidy-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-ltcs-ins-cards-card.vue', () => {
  const { mount } = setupComponentTest()
  const user = createUserStub()
  let wrapper: Wrapper<Vue & any>

  beforeAll(() => {
    const items = createLtcsSubsidyStubsForUser(user.id)
    const propsData = { items, user }
    wrapper = mount(ZLtcsSubsidiesCard, {
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

  it('should be create itemLink', () => {
    const item = { id: 3 }
    const itemLink = wrapper.vm.tableOptions.itemLink(item)
    const expectedLink = `/users/${user.id}/ltcs-subsidies/${item.id}`
    expect(itemLink).toBe(expectedLink)
  })
})
