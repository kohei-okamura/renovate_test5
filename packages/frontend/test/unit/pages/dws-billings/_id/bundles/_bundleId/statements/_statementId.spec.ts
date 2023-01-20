/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { DwsBillingStatementStore } from '~/composables/stores/use-dws-billing-statement-store'
import { NuxtContext } from '~/models/nuxt'
import DwsBillingStatementStoreProviderPage
  from '~/pages/dws-billings/_id/bundles/_bundleId/statements/_statementId.vue'
import { createDwsBillingStatementStoreStub } from '~~/stubs/create-dws-billing-statement-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/dws-billings/_id/bundles/_bundleId/statements/_statementId.vue', () => {
  type RouterParameters = {
    id: string
    bundleId: string
    statementId: string
  }
  const createRouterParams = (params?: Partial<RouterParameters>): RouterParameters => {
    return {
      ...{
        id: '10',
        bundleId: '20',
        statementId: '30'
      },
      ...params
    }
  }
  const createApiParams = (params: RouterParameters) => {
    return {
      billingId: +params.id,
      bundleId: +params.bundleId,
      id: +params.statementId
    }
  }
  const { mount } = setupComponentTest()
  const params = createRouterParams()
  const $route = createMockedRoute({ params })
  const mocks = {
    $route
  }
  let store: DwsBillingStatementStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(DwsBillingStatementStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createDwsBillingStatementStoreStub()
    jest.spyOn(store, 'get').mockResolvedValue()
    await mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should call dwsBillingStatementStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith(createApiParams(params))
  })

  describe('validate', () => {
    it('should return true when valid statementId given', () => {
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric statementId given', () => {
      const params = createRouterParams({ statementId: 'abc' })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when statementId not given', () => {
      const params = {}
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
