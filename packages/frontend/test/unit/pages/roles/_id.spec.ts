/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { RoleStore } from '~/composables/stores/use-role-store'
import { NuxtContext } from '~/models/nuxt'
import RoleStoreProviderPage from '~/pages/roles/_id.vue'
import { createRoleStoreStub } from '~~/stubs/create-role-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/roles/_id.vue', () => {
  const { mount } = setupComponentTest()
  const $route = createMockedRoute({ params: { id: '517' } })
  const mocks = {
    $route
  }
  let store: RoleStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(RoleStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createRoleStoreStub()
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

  it('should call roleStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith({ id: 517 })
  })

  describe('validate', () => {
    it('should return true when valid id given', () => {
      const params = { id: '517' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric id given', () => {
      const params = { id: 'abc' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when id not given', () => {
      const params = {}
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
