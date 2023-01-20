/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZHomeVisitLongTermCareCalcSpecForm
  from '~/components/domain/office/z-home-visit-long-term-care-calc-spec-form.vue'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-home-visit-long-term-care-calc-spec-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const form: HomeVisitLongTermCareCalcSpecsApi.Form = {
    period: {
      start: '1987-11-08',
      end: '2025-01-03'
    },
    locationAddition: 2,
    specifiedOfficeAddition: 4,
    treatmentImprovementAddition: 0,
    specifiedTreatmentImprovementAddition: 1,
    baseIncreaseSupportAddition: 1
  }
  const propsData = {
    buttonText: '登録',
    errors: {},
    progress: false,
    office: createOfficeStub(),
    value: { ...form }
  }

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZHomeVisitLongTermCareCalcSpecForm, {
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

    async function validate (values: Partial<HomeVisitLongTermCareCalcSpecsApi.Form> = {}) {
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

    it('should fail when locationAddition is empty', async () => {
      await validate({
        locationAddition: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-location-addition] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when specifiedOfficeAddition is empty', async () => {
      await validate({
        specifiedOfficeAddition: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-specified-office-addition] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when treatmentImprovementAddition is empty', async () => {
      await validate({
        treatmentImprovementAddition: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-treatment-improvement-addition] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when specifiedTreatmentImprovementAddition is empty', async () => {
      await validate({
        specifiedTreatmentImprovementAddition: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-specified-treatment-improvement-addition] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when baseIncreaseSupportAddition is empty', async () => {
      await validate({
        baseIncreaseSupportAddition: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-base-increase-support-addition] .v-messages').text()).toBe('入力してください。')
    })
  })
})
