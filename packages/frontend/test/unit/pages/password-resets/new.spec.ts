/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { camelToKebab } from '@zinger/helpers'
import Vue from 'vue'
import { HttpStatusCode } from '~/models/http-status-code'
import PasswordResetsNewPage from '~/pages/password-resets/new.vue'
import { Plugins } from '~/plugins'
import { PasswordResetsApi } from '~/services/api/password-resets-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

describe('pages/password-resets/new.vue', () => {
  const { mount } = setupComponentTest()
  const formValues: PasswordResetsApi.CreateForm = {
    email: 'john@example.com'
  }
  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(PasswordResetsNewPage, options)
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<PasswordResetsApi.CreateForm> = {}): Promise<void> {
      await setData(wrapper, { form: { ...formValues, ...values } })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should fail when email is empty', async () => {
      await validate({
        email: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-email] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when email is not a valid email address', async () => {
      await validate({
        email: 'this is not an email address'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-email] .v-messages').text()).toBe('有効なメールアドレスを入力してください。')
    })
  })

  describe('submit', () => {
    const $api = createMockedApi('passwordResets')
    const form = { ...formValues }
    const mocks: Partial<Plugins> = {
      $api
    }

    beforeEach(() => {
      mountComponent({ mocks })
    })

    afterEach(() => {
      unmountComponent()
      jest.clearAllMocks()
    })

    it('should call $api.passwordResets.create', async () => {
      jest.spyOn($api.passwordResets, 'create').mockResolvedValue(undefined)
      await setData(wrapper, { form })

      await submit(() => wrapper.find('[data-form]'))

      expect($api.passwordResets.create).toHaveBeenCalledTimes(1)
      expect($api.passwordResets.create).toHaveBeenCalledWith({ form })
    })

    it('should not dispatch any action when validation failure', async () => {
      await submit(() => wrapper.find('[data-form]'))
      expect($api.passwordResets.create).not.toHaveBeenCalled()
    })

    it('should display message when succeeded', async () => {
      jest.spyOn($api.passwordResets, 'create').mockResolvedValue(undefined)
      await setData(wrapper, { form })

      await submit(() => wrapper.find('[data-form]'))

      expect(wrapper).toMatchSnapshot()
    })

    it.each([
      ['email', 'メールアドレスを入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.passwordResets, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await setData(wrapper, { form })
        await submit(() => wrapper.find('[data-form]'))

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
