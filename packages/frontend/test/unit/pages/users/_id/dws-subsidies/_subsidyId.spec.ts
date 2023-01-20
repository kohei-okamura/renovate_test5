/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { DwsSubsidyStore } from '~/composables/stores/use-dws-subsidy-store'
import { NuxtContext } from '~/models/nuxt'
import DwsSubsidyStoreProviderPage from '~/pages/users/_id/dws-subsidies/_subsidyId.vue'
import { createDwsSubsidyStoreStub } from '~~/stubs/create-dws-subsidy-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/dws-subsidies/_subsidyId.vue', () => {
  const { mount } = setupComponentTest()
  const $route = createMockedRoute({ params: { id: '517', subsidyId: '604' } })
  const mocks = {
    $route
  }
  let store: DwsSubsidyStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(DwsSubsidyStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createDwsSubsidyStoreStub()
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

  it('should call dwsSubsidyStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith({ id: 604, userId: 517 })
  })

  describe('validate', () => {
    it('should return true when valid subsidyId and id given', () => {
      const params = { id: '517', subsidyId: '604' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric subsidyId given', () => {
      const params = { id: '517', subsidyId: 'abc' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when non-numeric id given', () => {
      const params = { id: 'abc', subsidyId: '604' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when numeric subsidyId not given', () => {
      const params = { id: '517' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when numeric id not given', () => {
      const params = { subsidyId: '604' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
