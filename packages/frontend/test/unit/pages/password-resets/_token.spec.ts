/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import { HttpStatusCode } from '~/models/http-status-code'
import PasswordResetsCommitPage from '~/pages/password-resets/_token.vue'
import { PasswordResetsApi } from '~/services/api/password-resets-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

describe('pages/password-resets/_token.vue', () => {
  const { mount } = setupComponentTest()
  const token = 'x'.repeat(60)
  const params = { token }
  const $api = createMockedApi('passwordResets')
  const $route = createMockedRoute({ params })
  const $router = createMockedRouter()
  const mocks = {
    $api,
    $route,
    $router
  }
  let wrapper: Wrapper<Vue & any>

  async function mountComponent () {
    wrapper = mount(PasswordResetsCommitPage, { mocks })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    jest.spyOn($api.passwordResets, 'verify').mockResolvedValue(undefined)
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should display message when token verified', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should display message when server responses 403 Forbidden', async () => {
    jest.spyOn($api.passwordResets, 'verify').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validate', () => {
    const context: { params: Dictionary } = {
      params: { ...params }
    }

    beforeAll(async () => {
      await mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should return true when valid token given', () => {
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should return false when valid token not given', () => {
      context.params.token = 'x'.repeat(59)
      expect(wrapper.vm.$options.validate!(context)).toBeFalse()

      context.params.token = 'x'.repeat(61)
      expect(wrapper.vm.$options.validate!(context)).toBeFalse()

      context.params.token = '-'.repeat(60)
      expect(wrapper.vm.$options.validate!(context)).toBeFalse()

      context.params = {}
      expect(wrapper.vm.$options.validate!(context)).toBeFalse()
    })
  })

  describe('validation', () => {
    const formValues: PasswordResetsApi.CommitForm = {
      password: 'PaSSWoRD'
    }

    async function validate (values: Partial<PasswordResetsApi.CommitForm> = {}): Promise<ValidationObserverInstance> {
      const form = {
        ...formValues,
        ...values
      }
      await setData(wrapper, { form, verified: true })
      const observer = getValidationObserver(wrapper)
      await observer.validate()
      jest.runOnlyPendingTimers()
      return observer
    }

    beforeAll(async () => {
      await mountComponent()
      jest.spyOn($api.passwordResets, 'verify').mockResolvedValueOnce()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      const observer = await validate()
      expect(observer).toBePassed()
    })

    it('should fail when password is empty', async () => {
      const observer = await validate({ password: '' })
      expect(observer).not.toBePassed()
    })

    it('should fail when password.length < 8', async () => {
      let observer: ValidationObserverInstance
      observer = await validate({ password: 'x'.repeat(7) })
      expect(observer).not.toBePassed()
      observer = await validate({ password: 'x'.repeat(8) })
      expect(observer).toBePassed()
    })
  })

  describe('submit', () => {
    const form = {
      password: 'PaSSWoRD'
    }

    beforeEach(async () => {
      await mountComponent()
      jest.spyOn($api.passwordResets, 'commit').mockResolvedValue(undefined)
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should run validation', async () => {
      await submit(() => wrapper.find('[data-form]'))
      expect(wrapper).toMatchSnapshot()
    })

    it('should not call api when validation failed', async () => {
      await submit(() => wrapper.find('[data-form]'))
      expect($api.passwordResets.commit).not.toHaveBeenCalled()
    })

    it('should call $api.passwordResets.commit when validation succeeded', async () => {
      await setData(wrapper, { form })

      await submit(() => wrapper.find('[data-form]'))

      expect($api.passwordResets.commit).toHaveBeenCalledTimes(1)
      expect($api.passwordResets.commit).toHaveBeenCalledWith({ form, token })
    })

    it('should display message when api responses 2xx', async () => {
      await setData(wrapper, { form })

      await submit(() => wrapper.find('[data-form]'))

      expect(wrapper).toMatchSnapshot()
    })

    it('should display errors when api responses bad request', async () => {
      jest.spyOn($api.passwordResets, 'commit').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
        errors: {
          password: ['パスワードを入力してください。']
        }
      }))
      await setData(wrapper, { form })

      await submit(() => wrapper.find('[data-form]'))

      expect(wrapper.vm.committed).toBeFalse()
      expect(wrapper).toMatchSnapshot()
    })
  })
})
