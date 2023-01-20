/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZLtcsBillingStatusAggregateCard from '~/components/domain/billing/z-ltcs-billing-status-aggregate-card.vue'
import { LtcsBillingStoreStatusAggregate } from '~/composables/stores/use-ltcs-billing-store'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-ltcs-billing-status-aggregate-card.vue', () => {
  const { mount } = setupComponentTest()
  const aggregate: LtcsBillingStoreStatusAggregate = {
    '2021-02': {
      checking: 0,
      ready: 0,
      fixed: 19,
      total: 19
    },
    '2021-03': {
      checking: 12,
      ready: 5,
      fixed: 24,
      total: 41
    }
  }

  let wrapper: Wrapper<Vue>

  function mountComponent (options: Partial<MountOptions<Vue>> = {}) {
    wrapper = mount(ZLtcsBillingStatusAggregateCard, options)
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it.each([
    ['should be rendered correctly', aggregate, true],
    ['should be rendered correctly when it is not have statements', aggregate, false]
  ])('%s', (_, aggregate, hasStatements) => {
    const propsData = {
      aggregate,
      hasStatements
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })
})
