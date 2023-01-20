/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZLtcsBillingStatementItemListCard from '~/components/domain/billing/z-ltcs-billing-statement-item-list-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createLtcsBillingStatementResponseStub } from '~~/stubs/create-ltcs-billing-statement-response-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-ltcs-billing-statement-item-list-card.vue', () => {
  const { mount } = setupComponentTest()
  const { billing, statement } = createLtcsBillingStatementResponseStub()

  let wrapper: Wrapper<Vue>

  function mountComponent (options: Partial<MountOptions<Vue>> = {}) {
    wrapper = mount(ZLtcsBillingStatementItemListCard, {
      ...options,
      ...provides([sessionStoreKey, createAuthStub({ isSystemAdmin: true })])
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    const propsData = {
      items: statement.items,
      officeId: billing.office.officeId,
      providedIn: '2021-02'
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })
})
