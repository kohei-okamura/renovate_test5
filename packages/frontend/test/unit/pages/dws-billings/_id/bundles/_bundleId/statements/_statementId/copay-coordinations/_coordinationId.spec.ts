/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { DwsBillingCopayCoordinationStore } from '~/composables/stores/use-dws-billing-copay-coordination-store'
import { NuxtContext } from '~/models/nuxt'
import DwsBillingCopayCoordinationStoreProviderPage
  from '~/pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/copay-coordinations/_coordinationId.vue'
import { createDwsBillingCopayCoordinationStoreStub } from '~~/stubs/create-dws-billing-copay-coordination-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/copay-coordinations/_coordinationId.vue', () => {
  type RouterParameters = {
    id: string
    bundleId: string
    coordinationId: string
  }
  const createRouterParams = (params?: Partial<RouterParameters>): RouterParameters => {
    return {
      ...{
        id: '10',
        bundleId: '20',
        coordinationId: '30'
      },
      ...params
    }
  }
  const createApiParams = (params: RouterParameters) => {
    return {
      billingId: +params.id,
      bundleId: +params.bundleId,
      id: +params.coordinationId
    }
  }
  const { mount } = setupComponentTest()
  const params = createRouterParams()
  const $route = createMockedRoute({ params })
  const mocks = {
    $route
  }
  let store: DwsBillingCopayCoordinationStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(DwsBillingCopayCoordinationStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createDwsBillingCopayCoordinationStoreStub()
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

  it('should call dwsBillingCopayCoordinationStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith(createApiParams(params))
  })

  describe('validate', () => {
    it('should return true when valid parameters (id, bundleId, coordinationId) given', () => {
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it.each([
      ['id'],
      ['bundleId'],
      ['coordinationId']
    ])('should return false when non-numeric %s given', key => {
      const params = createRouterParams({ [key]: 'abc' })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it.each([
      ['id'],
      ['bundleId'],
      ['coordinationId']
    ])('should return false when %s not given', key => {
      const params = createRouterParams({ [key]: undefined })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
