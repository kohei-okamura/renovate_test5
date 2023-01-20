/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { LtcsInsCardStore } from '~/composables/stores/use-ltcs-ins-card-store'
import { NuxtContext } from '~/models/nuxt'
import LtcsInsCardStoreProviderPage from '~/pages/users/_id/ltcs-ins-cards/_cardId.vue'
import { createLtcsInsCardStoreStub } from '~~/stubs/create-ltcs-ins-card-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/ltcs-ins-cards/_cardId.vue', () => {
  const { mount } = setupComponentTest()
  const $route = createMockedRoute({ params: { id: '517', cardId: '604' } })
  const mocks = {
    $route
  }
  let store: LtcsInsCardStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(LtcsInsCardStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createLtcsInsCardStoreStub()
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

  it('should call ltcsInsCardStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith({ id: 604, userId: 517 })
  })

  describe('validate', () => {
    it('should return true when valid cardId given', () => {
      const params = { id: '517', cardId: '604' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric cardId given', () => {
      const params = { id: '517', cardId: 'abc' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when cardId not given', () => {
      const params = { id: '517' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
