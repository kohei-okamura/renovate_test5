/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { DwsBillingServiceReportStore } from '~/composables/stores/use-dws-billing-service-report-store'
import { NuxtContext } from '~/models/nuxt'
import DwsBillingServiceReportStoreProviderPage from '~/pages/dws-billings/_id/bundles/_bundleId/reports/_reportId.vue'
import { createDwsBillingServiceReportStoreStub } from '~~/stubs/create-dws-billing-service-report-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/dws-billings/_id/bundles/_bundleId/reports/_reportId.vue', () => {
  type RouterParameters = {
    id: string
    bundleId: string
    reportId: string
  }
  const createRouterParams = (params?: Partial<RouterParameters>): RouterParameters => {
    return {
      ...{
        id: '10',
        bundleId: '20',
        reportId: '30'
      },
      ...params
    }
  }
  const createApiParams = (params: RouterParameters) => {
    return {
      billingId: +params.id,
      bundleId: +params.bundleId,
      id: +params.reportId
    }
  }
  const { mount } = setupComponentTest()
  const params = createRouterParams()
  const $route = createMockedRoute({ params })
  const mocks = {
    $route
  }
  let store: DwsBillingServiceReportStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(DwsBillingServiceReportStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createDwsBillingServiceReportStoreStub()
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

  it('should call dwsBillingReportStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith(createApiParams(params))
  })

  describe('validate', () => {
    it('should return true when valid parameters (id, bundleId, reportId) given', () => {
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it.each([
      ['id'],
      ['bundleId'],
      ['reportId']
    ])('should return false when non-numeric %s given', key => {
      const params = createRouterParams({ [key]: 'abc' })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it.each([
      ['id'],
      ['bundleId'],
      ['reportId']
    ])('should return false when %s not given', key => {
      const params = createRouterParams({ [key]: undefined })
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
