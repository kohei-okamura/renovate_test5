/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { DwsCertificationStore } from '~/composables/stores/use-dws-certification-store'
import { NuxtContext } from '~/models/nuxt'
import DwsCertificationStoreProviderPage from '~/pages/users/_id/dws-certifications/_certificationId.vue'
import { createDwsCertificationStoreStub } from '~~/stubs/create-dws-certification-store-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/dws-certifications/_certificationId.vue', () => {
  const { shallowMount } = setupComponentTest()
  const $route = createMockedRoute({ params: { id: '517', certificationId: '604' } })
  const mocks = {
    $route
  }
  let store: DwsCertificationStore
  let wrapper: Wrapper<Vue>

  async function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = shallowMount(DwsCertificationStoreProviderPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    store = createDwsCertificationStoreStub()
    jest.spyOn(store, 'get').mockResolvedValue()
    await mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    mocked(store.get).mockRestore()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should call dwsCertificationStore.get', () => {
    expect(store.get).toHaveBeenCalledTimes(1)
    expect(store.get).toHaveBeenCalledWith({ id: 604, userId: 517 })
  })

  describe('validate', () => {
    it('should return true when valid certificationId given', () => {
      const params = { id: '517', certificationId: '604' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when non-numeric certificationId given', () => {
      const params = { id: '517', certificationId: 'abc' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })

    it('should return false when certificationId not given', () => {
      const params = { id: '517' }
      const context = createMock<NuxtContext>({ params })
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeFalse()
    })
  })
})
