/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue, { ComponentOptions } from 'vue'
import { homeHelpServiceCalcSpecStateKey } from '~/composables/stores/use-home-help-service-calc-spec-store'
import { officeStateKey, officeStoreKey } from '~/composables/stores/use-office-store'
import { HttpStatusCode } from '~/models/http-status-code'
import HomeHelpServiceCalcSpecsEditPage from '~/pages/offices/_id/home-help-service-calc-specs/_specId/edit.vue'
import { HomeHelpServiceCalcSpecsApi } from '~/services/api/home-help-service-calc-specs-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createHomeHelpServiceCalcSpecResponseStub } from '~~/stubs/create-home-help-service-calc-spec-response-stub'
import { createHomeHelpServiceCalcSpecStoreStub } from '~~/stubs/create-home-help-service-calc-spec-store-stub'
import { createHomeHelpServiceCalcSpecStub } from '~~/stubs/create-home-help-service-calc-spec-stub'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'
import { createOfficeStoreStub } from '~~/stubs/create-office-store-stub'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('/pages/offices/_id/home-help-service-calc-specs/_specId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('homeHelpServiceCalcSpecs', 'offices')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: HomeHelpServiceCalcSpecsApi.Form = {
    period: {
      start: '1987-11-08',
      end: '2025-01-03'
    },
    specifiedOfficeAddition: 4,
    treatmentImprovementAddition: 0,
    specifiedTreatmentImprovementAddition: 1
  }
  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }
  const stub = createHomeHelpServiceCalcSpecStub()
  const officeStub = createOfficeStub()
  let wrapper: Wrapper<Vue & any>
  afterEach(() => {
    jest.clearAllMocks()
  })

  function mountComponent (options: ComponentOptions<Vue> = {}) {
    const homeHelpServiceCalcSpecResponse = createHomeHelpServiceCalcSpecResponseStub(stub.id)
    const homeHelpServiceCalcSpecStore =
      createHomeHelpServiceCalcSpecStoreStub(homeHelpServiceCalcSpecResponse)
    const response = createOfficeResponseStub(officeStub.id)
    const officeStore = createOfficeStoreStub(response)
    wrapper = mount(HomeHelpServiceCalcSpecsEditPage, {
      ...options,
      ...provides(
        [homeHelpServiceCalcSpecStateKey, homeHelpServiceCalcSpecStore.state],
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
      jest.spyOn($api.homeHelpServiceCalcSpecs, 'update').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      jest.clearAllMocks()
    })

    it('should call $api.homeHelpServiceCalcSpecs.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.homeHelpServiceCalcSpecs.update).toHaveBeenCalledTimes(1)
      expect($api.homeHelpServiceCalcSpecs.update).toHaveBeenCalledWith({
        form,
        id: stub.id,
        officeId: officeStub.id
      })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('算定情報（障害・居宅介護）を編集しました。')
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
        jest.spyOn($api.homeHelpServiceCalcSpecs, 'update').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
