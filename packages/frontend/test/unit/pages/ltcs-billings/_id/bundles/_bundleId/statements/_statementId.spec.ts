/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { LtcsBillingStatementStore } from '~/composables/stores/use-ltcs-billing-statement-store'
import { NuxtContext } from '~/models/nuxt'
import LtcsBillingStatementStoreProviderPage
  from '~/pages/ltcs-billings/_id/bundles/_bundleId/statements/_statementId.vue'
import { createLtcsBillingStatementStoreStub } from '~~/stubs/create-ltcs-billing-statement-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/ltcs-billings/_id/bundles/_bundleId/statements/_statementId.vue', () => {
  const { mount } = setupComponentTest()

  const params = {
    id: '10',
    bundleId: '20',
    statementId: '30'
  }
  const $route = createMockedRoute({ params })
  const mocks = {
    $route
  }
  let store: LtcsBillingStatementStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(LtcsBillingStatementStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createLtcsBillingStatementStoreStub()
    jest.spyOn(store, 'get').mockResolvedValue()
    await mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    mocked(store.get).mockReset()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should call ltcsBillingStatementStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith({
      billingId: +params.id,
      bundleId: +params.bundleId,
      id: +params.statementId
    })
  })

  describe('validate', () => {
    it('should return true when valid statementId given', () => {
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric statementId given', () => {
      const params = {
        id: '10',
        bundleId: '20',
        statementId: 'ABC'
      }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when statementId not given', () => {
      const params = {
        statementId: 'ABC'
      }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
