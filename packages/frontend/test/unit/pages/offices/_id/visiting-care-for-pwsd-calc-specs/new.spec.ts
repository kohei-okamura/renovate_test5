/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { officeStateKey, officeStoreKey } from '~/composables/stores/use-office-store'
import { HttpStatusCode } from '~/models/http-status-code'
import VisitingCareForPwsdCalcSpecNewPage from '~/pages/offices/_id/visiting-care-for-pwsd-calc-specs/new.vue'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'
import { createOfficeStoreStub } from '~~/stubs/create-office-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/offices/_id/visiting-care-for-pwsd-calc-specs/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('visitingCareForPwsdCalcSpecs', 'offices')
  const $back = createMockedBack()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: VisitingCareForPwsdCalcSpecsApi.Form = {
    period: {
      start: '1987-11-08',
      end: '2025-01-03'
    },
    specifiedOfficeAddition: 3,
    treatmentImprovementAddition: 0,
    specifiedTreatmentImprovementAddition: 1
  }
  const mocks = {
    $api,
    $back,
    $form,
    $snackbar
  }
  const officeId = 1
  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    const response = createOfficeResponseStub(officeId)
    const officeStore = createOfficeStoreStub(response)
    wrapper = mount(VisitingCareForPwsdCalcSpecNewPage, {
      ...provides(
        [officeStateKey, officeStore.state],
        [officeStoreKey, officeStore]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    mountComponent()
  })

  afterEach(() => {
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeEach(() => {
      jest.spyOn($api.visitingCareForPwsdCalcSpecs, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($api.visitingCareForPwsdCalcSpecs.create).mockReset()
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
    })

    it('should call $api.visitingCareForPwsdCalcSpecs.create', async () => {
      await wrapper.vm.submit(form)

      expect($api.visitingCareForPwsdCalcSpecs.create).toHaveBeenCalledTimes(1)
      expect($api.visitingCareForPwsdCalcSpecs.create).toHaveBeenCalledWith({ form, officeId })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('算定情報（障害・重度訪問介護）を登録しました。')
    })

    it.each([
      ['periodStart', '適用期間（開始）を入力してください。'],
      ['periodEnd', '適用期間（終了）を入力してください。'],
      ['specifiedOfficeAddition', '特定事業所加算を入力してください。'],
      ['treatmentImprovementAddition', '処遇改善加算を入力してください。'],
      ['specifiedTreatmentImprovementAddition', '特定処遇改善加算を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.visitingCareForPwsdCalcSpecs, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
