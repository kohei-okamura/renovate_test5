/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { assign } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import ZDwsSubsidyForm from '~/components/domain/user/z-dws-subsidy-form.vue'
import { dwsSubsidyStateKey } from '~/composables/stores/use-dws-subsidy-store'
import { Plugins } from '~/plugins'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createDwsSubsidyResponseStub } from '~~/stubs/create-dws-subsidy-response-stub'
import { createDwsSubsidyStoreStub } from '~~/stubs/create-dws-subsidy-store-stub'
import { createDwsSubsidyStub } from '~~/stubs/create-dws-subsidy-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-dws-subsidy-form.vue', () => {
  const { mount } = setupComponentTest()
  const $confirm = createMock<ConfirmDialogService>()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()

  const form: DwsSubsidiesApi.Form = {
    period: {
      start: '1976-09-13',
      end: '1982-11-14'
    },
    cityName: '東伯郡琴浦町',
    cityCode: '340331',
    subsidyType: UserDwsSubsidyType.benefitRate,
    factor: UserDwsSubsidyFactor.copay,
    copayRate: undefined as any,
    benefitRate: 69,
    rounding: Rounding.floor,
    // TODO 値が未入力であることを表すためundefinedとする
    benefitAmount: undefined as any,
    copayAmount: undefined as any,
    note: '鶏白湯に塩だった。もやしはメンマと、醤油がタンメンは背脂を注文する。魚粉が醤油を注文する。野菜が好きだ。担々麺にしよう。わかめに雲呑も好きだ。こってりの替え玉がタマネギマシマシで。'
  }
  const mocks: Partial<Plugins> = {
    $confirm,
    $form,
    $snackbar
  }
  const propsData = {
    buttonText: '登録',
    errors: {},
    progress: false,
    user: createUserStub(),
    value: { ...form }
  }
  let wrapper: Wrapper<Vue & any>

  const stub = createDwsSubsidyStub()
  const dwsSubsidyResponse = createDwsSubsidyResponseStub(stub.id)
  const dwsSubsidyStore = createDwsSubsidyStoreStub(dwsSubsidyResponse)

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZDwsSubsidyForm, {
      ...options,
      mocks,
      ...provides(
        [dwsSubsidyStateKey, dwsSubsidyStore.state]
      ),
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.spyOn($confirm, 'show').mockResolvedValue(true)
    jest.spyOn($snackbar, 'success').mockReturnValue()
  })

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

    async function validate (values?: Partial<DwsSubsidiesApi.Form>) {
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

    it('should fail when period end is before period start', async () => {
      await validate({
        period: {
          start: '1976-09-13',
          end: '1976-09-12'
        }
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-period-start] .v-messages').text()).toBe('開始日より終了日の日付を後にしてください。')
      expect(wrapper.find('[data-period-end] .v-messages').text()).toBe('開始日より終了日の日付を後にしてください。')
    })

    it('should fail when cityName is empty', async () => {
      await validate({
        cityName: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-city-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when cityName is longer than 200', async () => {
      await validate({
        period: {
          start: '1976-09-13',
          end: '1982-11-14'
        },
        cityName: '三'.repeat(200)
      })
      expect(observer).toBePassed()

      await validate({
        cityName: '三'.repeat(201)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-city-name] .v-messages').text()).toBe('200文字以内で入力してください。')
    })

    it('should fail when cityCode is empty', async () => {
      await validate({
        cityCode: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-city-code] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when cityCode is other than 6 digits', async () => {
      await validate({
        cityCode: '2'.repeat(6)
      })
      expect(observer).toBePassed()

      await validate({
        cityCode: '2'.repeat(5)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-city-code] .v-messages').text()).toBe('6桁の半角数字で入力してください。')
    })

    it('should fail when subsidyType is empty', async () => {
      await validate({
        subsidyType: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-subsidy-type] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when factor is empty', async () => {
      await validate({
        subsidyType: UserDwsSubsidyType.benefitRate,
        factor: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-factor] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when benefitRate is empty', async () => {
      await validate({
        subsidyType: UserDwsSubsidyType.benefitRate,
        benefitRate: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-benefit-rate] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when copayRate is empty', async () => {
      await validate({
        subsidyType: UserDwsSubsidyType.copayRate,
        copayRate: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay-rate] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when rounding is empty', async () => {
      await validate({
        subsidyType: UserDwsSubsidyType.benefitRate,
        rounding: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-rounding] .v-messages').text()).toBe('入力してください。')
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

    it('should fail when copayRate is over than 101 number', async () => {
      await validate({
        copayRate: 100
      })
      expect(observer).toBePassed()

      await validate({
        subsidyType: UserDwsSubsidyType.copayRate,
        copayRate: 101
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay-rate] .v-messages').text()).toBe('1以上、100以下の半角数字で入力してください。')
    })

    it('should fail when benefitAmount is empty', async () => {
      await validate({
        subsidyType: UserDwsSubsidyType.benefitAmount,
        benefitAmount: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-benefit-amount] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric benefitAmount given', async () => {
      await validate({
        subsidyType: UserDwsSubsidyType.benefitAmount,
        benefitAmount: 'abc' as any
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-benefit-amount] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })

    it('should fail when copay is empty', async () => {
      await validate({
        subsidyType: UserDwsSubsidyType.copayAmount,
        copayAmount: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay-amount] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric copay given', async () => {
      await validate({
        subsidyType: UserDwsSubsidyType.copayAmount,
        copayAmount: 'abc' as any
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-copay-amount] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })

    it('should fail when note is longer than 255', async () => {
      await validate({
        note: '三'.repeat(255)
      })
      expect(observer).toBePassed()

      await validate({
        note: '三'.repeat(256)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-note] .v-messages').text()).toBe('255文字以内で入力してください。')
    })
  })

  describe('input form control', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should be remove benefitRate settings when subsidyType become other than UserDwsSubsidyType.benefitRate', async () => {
      const values = {
        subsidyType: UserDwsSubsidyType.benefitRate,
        factor: UserDwsSubsidyFactor.copay,
        benefitRate: 69,
        rounding: Rounding.floor
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.benefitRate).not.toBeUndefined()
      expect(form.factor).not.toBeUndefined()
      expect(form.rounding).not.toBeUndefined()
      await assign(form, { subsidyType: UserDwsSubsidyType.benefitAmount })
      expect(form.benefitRate).toBeUndefined()
      expect(form.factor).toBeUndefined()
      expect(form.rounding).toBeUndefined()
    })

    it('should be remove copayRate settings when subsidyType become other than UserDwsSubsidyType.copayRate', async () => {
      const values = {
        subsidyType: UserDwsSubsidyType.copayRate,
        factor: UserDwsSubsidyFactor.copay,
        copayRate: 69,
        rounding: Rounding.floor
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.copayRate).not.toBeUndefined()
      expect(form.factor).not.toBeUndefined()
      expect(form.rounding).not.toBeUndefined()
      await assign(form, { subsidyType: UserDwsSubsidyType.benefitAmount })
      expect(form.copayRate).toBeUndefined()
      expect(form.factor).toBeUndefined()
      expect(form.rounding).toBeUndefined()
    })

    it('should be remove benefitAmount settings when subsidyType become other than UserDwsSubsidyType.benefitAmount', async () => {
      const values = {
        subsidyType: UserDwsSubsidyType.benefitAmount,
        benefitAmount: 3403
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.benefitAmount).not.toBeUndefined()
      await assign(form, { subsidyType: UserDwsSubsidyType.copayAmount })
      expect(form.benefitRate).toBeUndefined()
    })

    it('should be remove copay settings when subsidyType become other than UserDwsSubsidyType.copayAmount', async () => {
      const values = {
        subsidyType: UserDwsSubsidyType.copayAmount,
        copayAmount: 1403
      }
      await setData(wrapper, { form: values })
      const form = wrapper.vm.$data.form
      expect(form.copayAmount).not.toBeUndefined()
      await assign(form, { subsidyType: UserDwsSubsidyType.benefitRate })
      expect(form.benefitRate).toBeUndefined()
    })
  })
})
