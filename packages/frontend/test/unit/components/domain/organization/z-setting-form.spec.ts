/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZSettingForm from '~/components/domain/organization/z-setting-form.vue'
import { SettingApi } from '~/services/api/setting-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-setting-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const propsData = {
    buttonText: '登録',
    errors: {},
    progress: false,
    value: {}
  }

  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZSettingForm, {
      ...options,
      mocks,
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper.element).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    const form: SettingApi.Form = {
      bankingClientCode: '1234567890'
    }
    let observer: ValidationObserverInstance

    async function validate (values: Partial<SettingApi.Form> = {}) {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
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

    it('should fail when bankingClientCode is empty', async () => {
      await validate({
        bankingClientCode: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-banking-client-code] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when bankingClientCode is not 10 digit', async () => {
      await validate({
        bankingClientCode: '1'.repeat(10)
      })
      expect(observer).toBePassed()

      await validate({
        bankingClientCode: '1'.repeat(9)
      })
      expect(observer).not.toBePassed()

      await validate({
        bankingClientCode: '1'.repeat(11)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-banking-client-code] .v-messages').text()).toBe('10桁の半角数字で入力してください。')
    })
  })
})
