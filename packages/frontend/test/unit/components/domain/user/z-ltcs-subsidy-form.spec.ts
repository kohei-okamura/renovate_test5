/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZLtcsSubsidyForm from '~/components/domain/user/z-ltcs-subsidy-form.vue'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-ltcs-ins-card-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const form: LtcsSubsidiesApi.Form = {
    period: {
      start: '1976-09-13',
      end: '1982-11-14'
    },
    defrayerCategory: 81,
    defrayerNumber: '19427456',
    recipientNumber: '5478276',
    benefitRate: 69,
    copay: 1303
  }
  const propsData = {
    buttonText: '登録',
    errors: {},
    progress: false,
    user: createUserStub(),
    value: { ...form }
  }
  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZLtcsSubsidyForm, {
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
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<LtcsSubsidiesApi.Form> = {}) {
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

    it('should fail when period start is empty', async () => {
      await validate({
        period: {
          start: '',
          end: '1982-11-14'
        }
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-period-start] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when period end is empty', async () => {
      await validate({
        period: {
          start: '1976-09-13',
          end: ''
        }
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-period-end] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when defrayerCategory is empty', async () => {
      await validate({
        defrayerCategory: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-defrayer-category] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when defrayerNumber is empty', async () => {
      await validate({
        defrayerNumber: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-defrayer-number] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when defrayerNumber is other than 8 digits', async () => {
      await validate({
        period: {
          start: '1976-09-13',
          end: '1982-11-14'
        },
        defrayerNumber: '2'.repeat(8)
      })
      expect(observer).toBePassed()

      await validate({
        defrayerNumber: '2'.repeat(9)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-defrayer-number] .v-messages').text()).toBe('8桁の半角数字で入力してください。')
    })

    it('should fail when recipientNumber is empty', async () => {
      await validate({
        recipientNumber: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-recipient-number] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when recipientNumber is other than 7 digits', async () => {
      await validate({
        recipientNumber: '2'.repeat(7)
      })
      expect(observer).toBePassed()

      await validate({
        recipientNumber: '2'.repeat(8)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-recipient-number] .v-messages').text()).toBe('7桁の半角数字で入力してください。')
    })

    it('should fail when benefitRate is empty', async () => {
      await validate({
        benefitRate: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-benefit-rate] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when benefitRate is over than 101 number', async () => {
      await validate({
        benefitRate: 100
      })
      expect(observer).toBePassed()

      await validate({
        benefitRate: 101
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-benefit-rate] .v-messages').text()).toBe('1以上、100以下の半角数字で入力してください。')
    })

    it('should fail when copay is empty', async () => {
      await validate({
        copay: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric copay given', async () => {
      await validate({
        copay: 'abc' as any
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })
  })
})
