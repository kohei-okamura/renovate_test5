/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { camelToKebab, noop } from '@zinger/helpers/index'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsSubsidyStateKey } from '~/composables/stores/use-dws-subsidy-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsSubsidyNewPage from '~/pages/users/_id/dws-subsidies/new.vue'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsSubsidyResponseStub } from '~~/stubs/create-dws-subsidy-response-stub'
import { createDwsSubsidyStoreStub } from '~~/stubs/create-dws-subsidy-store-stub'
import { createDwsSubsidyStub } from '~~/stubs/create-dws-subsidy-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/dws-subsidies/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('dwsSubsidies', 'users')
  const $back = createMockedBack()
  const $router = createMockedRouter()
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
    benefitRate: 69,
    rounding: Rounding.floor,
    // TODO 値が未入力であることを表すためundefinedとする
    benefitAmount: undefined as any,
    copayAmount: undefined as any,
    note: '鶏白湯に塩だった。もやしはメンマと、醤油がタンメンは背脂を注文する。魚粉が醤油を注文する。野菜が好きだ。担々麺にしよう。わかめに雲呑も好きだ。こってりの替え玉がタマネギマシマシで。'
  }
  const mocks = {
    $api,
    $form,
    $router,
    $back,
    $snackbar
  }

  const userId = USER_ID_MIN
  const stub = createDwsSubsidyStub()
  const dwsSubsidyResponse = createDwsSubsidyResponseStub(stub.id)
  const dwsSubsidyStore = createDwsSubsidyStoreStub(dwsSubsidyResponse)
  const userResponse = createUserResponseStub(userId)
  const userStore = createUserStoreStub(userResponse)
  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(DwsSubsidyNewPage, {
      ...options,
      ...provides(
        [dwsSubsidyStateKey, dwsSubsidyStore.state],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn($api.dwsSubsidies, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($api.dwsSubsidies.create).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
    })

    afterEach(() => {
      mocked($api.dwsSubsidies.create).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
    })

    it('should call $api.dwsSubsidies.create when pass the validation and subsidyType is benefitRate', async () => {
      const expectedForm = {
        ...form,
        benefitAmount: 0,
        copayAmount: 0
      }
      await wrapper.vm.submit(form)

      expect($api.dwsSubsidies.create).toHaveBeenCalledTimes(1)
      expect($api.dwsSubsidies.create).toHaveBeenCalledWith({ form: expectedForm, userId })
    })

    it('should call $api.dwsSubsidies.create when pass the validation and subsidyType is benefitAmount', async () => {
      const submitForm = {
        ...form,
        subsidyType: UserDwsSubsidyType.benefitAmount,
        benefitRate: undefined,
        benefitAmount: 1311,
        copayAmount: undefined
      }
      const expectedForm = {
        ...submitForm,
        benefitRate: 0,
        copayAmount: 0
      }
      await wrapper.vm.submit(submitForm)

      expect($api.dwsSubsidies.create).toHaveBeenCalledTimes(1)
      expect($api.dwsSubsidies.create).toHaveBeenCalledWith({ form: expectedForm, userId })
    })

    it('should call $api.dwsSubsidies.create when pass the validation and subsidyType is copay', async () => {
      const submitForm = {
        ...form,
        subsidyType: UserDwsSubsidyType.copayAmount,
        benefitRate: undefined,
        benefitAmount: undefined,
        copayAmount: 1311
      }
      const expectedForm = {
        ...submitForm,
        benefitRate: 0,
        benefitAmount: 0
      }
      await wrapper.vm.submit(submitForm)

      expect($api.dwsSubsidies.create).toHaveBeenCalledTimes(1)
      expect($api.dwsSubsidies.create).toHaveBeenCalledWith({ form: expectedForm, userId })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('自治体助成情報を登録しました。')
    })

    it.each([
      ['periodStart', '適用期間（開始）を入力してください。'],
      ['periodEnd', '適用期間（終了）を入力してください。'],
      ['cityName', '助成自治体名を入力してください。'],
      ['cityCode', '助成自治体番号を入力してください。'],
      ['subsidyType', '給付方式を入力してください。']
      // TODO 給付率、給付額、本人負担額は初期表示時点では非表示のため一旦テスト対象から除外する
      // ['benefitRate', '給付率を入力してください。'],
      // ['benefitAmount', '給付額を入力してください。'],
      // ['copayAmount', '本人負担額を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.dwsSubsidies, 'create').mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
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
