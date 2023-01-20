/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { HomeVisitLongTermCareCalcSpecStore } from '~/composables/stores/use-home-visit-long-term-care-calc-spec-store'
import { NuxtContext } from '~/models/nuxt'
import HomeVisitLongTermCareCalcSpecProviderPage
  from '~/pages/offices/_id/home-visit-long-term-care-calc-specs/_specId.vue'
import {
  createHomeVisitLongTermCareCalcSpecStoreStub
} from '~~/stubs/create-home-visit-long-term-care-calc-spec-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/offices/_id/home-visit-long-term-care-calc-specs/_specId.vue', () => {
  const { mount } = setupComponentTest()
  const $route = createMockedRoute({ params: { id: '3', specId: '2' } })
  const mocks = {
    $route
  }
  let store: HomeVisitLongTermCareCalcSpecStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(HomeVisitLongTermCareCalcSpecProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createHomeVisitLongTermCareCalcSpecStoreStub()
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

  it('should call homeVisitLongTermCareCalcSpecStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith({ id: 2, officeId: 3 })
  })

  describe('validate', () => {
    it('should return true when valid specId given', () => {
      const params = { id: '3', specId: '2' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric specId given', () => {
      const params = { id: '3', specId: 'abc' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when specId not given', () => {
      const params = { id: '3' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
