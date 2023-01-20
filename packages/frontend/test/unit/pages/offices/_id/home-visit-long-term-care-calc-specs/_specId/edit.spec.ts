/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue, { ComponentOptions } from 'vue'
import { colors } from '~/colors'
import {
  homeVisitLongTermCareCalcSpecStateKey
} from '~/composables/stores/use-home-visit-long-term-care-calc-spec-store'
import { officeStateKey, officeStoreKey } from '~/composables/stores/use-office-store'
import { HttpStatusCode } from '~/models/http-status-code'
import HomeVisitLongTermCareCalcSpecsEditPage
  from '~/pages/offices/_id/home-visit-long-term-care-calc-specs/_specId/edit.vue'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import {
  createHomeVisitLongTermCareCalcSpecPostOrPutResponseStub,
  createHomeVisitLongTermCareCalcSpecResponseStub
} from '~~/stubs/create-home-visit-long-term-care-calc-spec-response-stub'
import {
  createHomeVisitLongTermCareCalcSpecStoreStub
} from '~~/stubs/create-home-visit-long-term-care-calc-spec-store-stub'
import { createHomeVisitLongTermCareCalcSpecStub } from '~~/stubs/create-home-visit-long-term-care-calc-spec-stub'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'
import { createOfficeStoreStub } from '~~/stubs/create-office-store-stub'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

describe('/pages/offices/_id/home-visit-long-term-care-calc-specs/_specId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('homeVisitLongTermCareCalcSpecs', 'offices')
  const $confirm = createMock<ConfirmDialogService>()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
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
  const mocks = {
    $api,
    $confirm,
    $form,
    $router,
    $snackbar
  }
  const responseStub = createHomeVisitLongTermCareCalcSpecPostOrPutResponseStub()
  const stub = createHomeVisitLongTermCareCalcSpecStub()
  const officeStub = createOfficeStub()
  const officeId = officeStub.id
  const response = createOfficeResponseStub(officeId)
  const officeStore = createOfficeStoreStub(response)
  let wrapper: Wrapper<Vue & any>
  afterEach(() => {
    jest.clearAllMocks()
  })

  function mountComponent (options: ComponentOptions<Vue> = {}) {
    const homeVisitLongTermCareCalcSpecResponse = createHomeVisitLongTermCareCalcSpecResponseStub(stub.id)
    const homeVisitLongTermCareCalcSpecStore =
      createHomeVisitLongTermCareCalcSpecStoreStub(homeVisitLongTermCareCalcSpecResponse)
    wrapper = mount(HomeVisitLongTermCareCalcSpecsEditPage, {
      ...options,
      ...provides(
        [homeVisitLongTermCareCalcSpecStateKey, homeVisitLongTermCareCalcSpecStore.state],
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
      jest.spyOn(officeStore, 'get').mockResolvedValue()
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($api.homeVisitLongTermCareCalcSpecs.update).mockClear()
      mocked(officeStore.get).mockClear()
      mocked($confirm.show).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
    })

    it('should call $api.homeVisitLongTermCareSpecs.update when pass the validation', async () => {
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'update').mockResolvedValue(responseStub)
      await wrapper.vm.submit(form)

      expect($api.homeVisitLongTermCareCalcSpecs.update).toHaveBeenCalledTimes(1)
      expect($api.homeVisitLongTermCareCalcSpecs.update).toHaveBeenCalledWith({
        form,
        id: stub.id,
        officeId: officeStub.id
      })
    })

    it('should display message when succeeded', async () => {
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'update').mockResolvedValue(responseStub)
      await wrapper.vm.submit(form)

      await wrapper.vm.$nextTick()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('算定情報（介保・訪問介護）を編集しました。')
    })

    it('should display dialog when provisionReportCount is not zero', async () => {
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'update')
        .mockResolvedValue({ ...responseStub, provisionReportCount: 1 })
      await wrapper.vm.submit(form)
      await wrapper.vm.$nextTick()

      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.primary,
        message: '適用期間中に予実が見つかりました。\n新しい加算情報を反映する必要があります。\n\n予実の一覧に遷移しますか？',
        positive: '遷移'
      })
    })

    it('should router replace ltcs-provision-reports list page when confirmed', async () => {
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'update')
        .mockResolvedValue({ ...responseStub, provisionReportCount: 1 })
      await submit(() => wrapper.find('form'))

      expect($router.replace).toHaveBeenCalledTimes(1)
      expect($router.replace).toHaveBeenCalledWith(`/ltcs-provision-reports?page=1&officeId=${officeId}`)
    })

    it('should call $router and officeStore when not confirmed', async () => {
      mocked($confirm.show).mockResolvedValueOnce(false)
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'update')
        .mockResolvedValue({ ...responseStub, provisionReportCount: 1 })
      await submit(() => wrapper.find('form'))

      expect(officeStore.get).toHaveBeenCalledTimes(1)
      expect(officeStore.get).toHaveBeenCalledWith({ id: officeId })
      expect($router.replace).toHaveBeenCalledTimes(1)
      expect($router.replace).toHaveBeenCalledWith(`/offices/${officeId}#calc-specs`)
    })

    it.each([
      ['periodStart', '適用期間（開始）を入力してください。'],
      ['periodEnd', '適用期間（終了）を入力してください。'],
      ['locationAddition', '地域加算を入力してください。'],
      ['specifiedOfficeAddition', '特定事業所加算を入力してください。'],
      ['treatmentImprovementAddition', '処遇改善加算を入力してください。'],
      ['specifiedTreatmentImprovementAddition', '特定処遇改善加算を入力してください。'],
      ['baseIncreaseSupportAddition', 'ベースアップ等支援加算を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'update').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await submit(() => wrapper.find('form'))

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
