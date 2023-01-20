/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { TaxCategory } from '@zinger/enums/lib/tax-category'
import { TaxType } from '@zinger/enums/lib/tax-type'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { useOffices } from '~/composables/use-offices'
import { HttpStatusCode } from '~/models/http-status-code'
import OwnExpenseProgramsNewPage from '~/pages/own-expense-programs/new.vue'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { SnackbarService } from '~/services/snackbar-service'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/own-expense-programs/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ownExpensePrograms')
  const $back = createMockedBack()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const officeId = OFFICE_ID_MIN
  const form: OwnExpenseProgramsApi.Form = {
    officeId,
    name: '魚粉マシマシで。',
    durationMinutes: 24,
    fee: {
      taxExcluded: 1000,
      taxIncluded: 1100,
      taxType: TaxType.taxExcluded,
      taxCategory: TaxCategory.consumptionTax
    },
    note: 'サンマーメンも好きだ。'
  }
  const mocks = {
    $api,
    $back,
    $form,
    $router,
    $snackbar
  }

  let wrapper: Wrapper<Vue & any>

  function mountComponent (props = {}) {
    wrapper = mount(OwnExpenseProgramsNewPage, { propsData: { ...props }, mocks })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($api.ownExpensePrograms, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked($api.ownExpensePrograms.create).mockReset()
    })

    it('should call $api.ownExpensePrograms.create when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.ownExpensePrograms.create).toHaveBeenCalledTimes(1)
      expect($api.ownExpensePrograms.create).toHaveBeenCalledWith({ form })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('自費サービス情報を登録しました。')
    })

    it.each([
      ['name', '自費サービス名を入力してください。'],
      ['durationMinutes', '単位時間数を入力してください。'],
      ['feeTaxType', '課税区分を入力してください。', 'tax-type'],
      ['feeTaxExcluded', '費用（税抜）を入力してください。', 'tax-excluded'],
      ['feeTaxIncluded', '費用（税込）を入力してください。', 'tax-included'],
      ['feeTaxCategory', '税率区分を入力してください。', 'tax-category']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        jest.spyOn($api.ownExpensePrograms, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        wrapper.vm.value = { ...form }
        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })

  /*
 * 課税区分が非課税の時のみ表示される項目があるので、別ケースで補完する
 * 非課税以外の時と同じ項目については省略する
 */
  describe('submit(taxType is taxExempted)', () => {
    const taxExemptedForm = { ...form, fee: { ...form.fee, taxType: TaxType.taxExempted } }

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($api.ownExpensePrograms, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked($api.ownExpensePrograms.create).mockReset()
    })

    it.each([
      ['taxExemptedFee', '費用を入力してください。', 'tax-exempted-fee']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId) => {
        jest.spyOn($api.ownExpensePrograms, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        wrapper.vm.value = { ...taxExemptedForm }
        await wrapper.vm.submit(taxExemptedForm)
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
