/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZUserDwsCalcSpecsCard from '~/components/domain/user/z-user-dws-calc-specs-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createUserDwsCalcSpecStubsForUser } from '~~/stubs/create-user-dws-calc-spec-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-user-dws-calc-specs-card.vue', () => {
  const { mount } = setupComponentTest()
  const user = createUserStub()
  let wrapper: Wrapper<Vue & any>

  beforeAll(() => {
    const items = createUserDwsCalcSpecStubsForUser(user.id)
    const propsData = { items, user }
    wrapper = mount(ZUserDwsCalcSpecsCard, {
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
    const expectedLink = `/users/${user.id}/dws-calc-specs/${item.id}/edit`
    expect(itemLink).toBe(expectedLink)
  })
})
