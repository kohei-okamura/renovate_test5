/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { UserDwsCalcSpecStore } from '~/composables/stores/use-user-dws-calc-spec-store'
import { NuxtContext } from '~/models/nuxt'
import UserDwsCalcSpecStoreProviderPage from '~/pages/users/_id/dws-calc-specs/_calcSpecId.vue'
import { createUserDwsCalcSpecStoreStub } from '~~/stubs/create-user-dws-calc-spec-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/dws-calc-specs/_calcSpecId.vue', () => {
  const { mount } = setupComponentTest()
  const $route = createMockedRoute({ params: { id: '3', calcSpecId: '2' } })
  const mocks = {
    $route
  }
  let store: UserDwsCalcSpecStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(UserDwsCalcSpecStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createUserDwsCalcSpecStoreStub()
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

  it('should call userDwsCalcSpecStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith({ id: 2, userId: 3 })
  })

  describe('validate', () => {
    it('should return true when valid calcSpecId given', () => {
      const params = { id: '3', calcSpecId: '2' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric calcSpecId given', () => {
      const params = { id: '3', calcSpecId: 'abc' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when calcSpecId not given', () => {
      const params = { id: '3' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
