/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue, { ComponentOptions } from 'vue'
import { officeStateKey, officeStoreKey } from '~/composables/stores/use-office-store'
import { visitingCareForPwsdCalcSpecStateKey } from '~/composables/stores/use-visiting-care-for-pwsd-calc-spec-store'
import { HttpStatusCode } from '~/models/http-status-code'
import VisitingCareForPwsdCalcSpecsEditPage
  from '~/pages/offices/_id/visiting-care-for-pwsd-calc-specs/_specId/edit.vue'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'
import { createOfficeStoreStub } from '~~/stubs/create-office-store-stub'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import {
  createVisitingCareForPwsdCalcSpecResponseStub
} from '~~/stubs/create-visiting-care-for-pwsd-calc-spec-response-stub'
import { createVisitingCareForPwsdCalcSpecStoreStub } from '~~/stubs/create-visiting-care-for-pwsd-calc-spec-store-stub'
import { createVisitingCareForPwsdCalcSpecStub } from '~~/stubs/create-visiting-care-for-pwsd-calc-spec-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('/pages/offices/_id/visiting-care-for-pwsd-calc-spec/_specId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('visitingCareForPwsdCalcSpecs', 'offices')
  const $router = createMockedRouter()
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
    $form,
    $router,
    $snackbar
  }
  const stub = createVisitingCareForPwsdCalcSpecStub()
  const officeStub = createOfficeStub()
  let wrapper: Wrapper<Vue & any>
  afterEach(() => {
    jest.clearAllMocks()
  })

  function mountComponent (options: ComponentOptions<Vue> = {}) {
    const visitingCareForPwsdCalcSpecResponse = createVisitingCareForPwsdCalcSpecResponseStub(stub.id)
    const visitingCareForPwsdCalcSpecStore =
      createVisitingCareForPwsdCalcSpecStoreStub(visitingCareForPwsdCalcSpecResponse)
    const response = createOfficeResponseStub(officeStub.id)
    const officeStore = createOfficeStoreStub(response)
    wrapper = mount(VisitingCareForPwsdCalcSpecsEditPage, {
      ...options,
      ...provides(
        [visitingCareForPwsdCalcSpecStateKey, visitingCareForPwsdCalcSpecStore.state],
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
      jest.spyOn($api.visitingCareForPwsdCalcSpecs, 'update').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      jest.clearAllMocks()
    })

    it('should call $api.visitingCareForPwsdCalcSpecs.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.visitingCareForPwsdCalcSpecs.update).toHaveBeenCalledTimes(1)
      expect($api.visitingCareForPwsdCalcSpecs.update).toHaveBeenCalledWith({
        form,
        id: stub.id,
        officeId: officeStub.id
      })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('算定情報（障害・重度訪問介護）を編集しました。')
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
        jest.spyOn($api.visitingCareForPwsdCalcSpecs, 'update').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
