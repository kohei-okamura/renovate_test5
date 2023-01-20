/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZUserBillingItemCard from '~/components/domain/user-billing/z-user-billing-item-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createUserBillingStub } from '~~/stubs/create-user-billing-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-user-billing-item-card.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const userBilling = createUserBillingStub()

  const defaultPropsData = {
    userBilling
  }

  function mountComponent () {
    wrapper = mount(ZUserBillingItemCard, {
      propsData: defaultPropsData,
      mocks,
      ...provides([sessionStoreKey, createAuthStub({ isSystemAdmin: true })])
    })
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
  })
})
