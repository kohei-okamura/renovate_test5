/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { SessionStore } from '~/composables/stores/create-session-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { HttpStatusCode } from '~/models/http-status-code'
import IndexPage from '~/pages/index.vue'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

describe('pages/index.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const mocks = {
    $router
  }
  let store: SessionStore
  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    store = createSessionStoreStub()
    wrapper = mount(IndexPage, {
      ...provides([sessionStoreKey, store]),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should not be rendered unauthorized error when initial display', () => {
    mountComponent()
    expect(wrapper.find('[data-unauthorized-error]')).not.toExist()
    unmountComponent()
  })

  describe('submit', () => {
    const formValues = {
      email: 'john@example.com',
      password: 'awesome-password',
      rememberMe: false
    }

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      jest.clearAllMocks()
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn(store, 'create').mockResolvedValue()
    })

    afterEach(() => {
      mocked(store.create).mockReset()
    })

    it('should run validation', async () => {
      await wrapper.vm.submit()
      expect(wrapper).toMatchSnapshot()
    })

    it('should dispatch sessions.create when validation succeeded', async () => {
      jest.spyOn(store, 'create').mockResolvedValue(undefined)

      await wrapper.vm.submit(formValues)

      expect(store.create).toHaveBeenCalledTimes(1)
      expect(store.create).toHaveBeenNthCalledWith(1, { form: formValues })
    })

    it('should display unauthorized error when api responses unauthorized', async () => {
      const error = createAxiosError(HttpStatusCode.Unauthorized)
      jest.spyOn(store, 'create').mockRejectedValue(error)
      jest.spyOn($router, 'push')

      await wrapper.vm.submit(formValues)

      expect($router.push).not.toHaveBeenCalled()
      expect(wrapper.find('[data-unauthorized-error]')).toExist()
    })

    it.skip('should display errors when api responses bad request', async () => {
      const error = createAxiosError(HttpStatusCode.BadRequest, {
        errors: {
          email: ['メールアドレスを入力してください。'],
          password: ['パスワードを入力してください。']
        }
      })
      jest.spyOn(store, 'create').mockRejectedValue(error)
      jest.spyOn($router, 'push')

      await submit(() => wrapper.find('form'))

      expect($router.push).not.toHaveBeenCalled()
      expect(wrapper).toMatchSnapshot()
    })

    it.skip('should redirect to dashboard when api responses ok/created', async () => {
      jest.spyOn(store, 'create').mockImplementation(() => {
        // store.state.isActive = computed(() => true)
        return Promise.resolve()
      })

      await wrapper.vm.submit()

      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith({ path: '/dashboard' })
    })
  })
})
