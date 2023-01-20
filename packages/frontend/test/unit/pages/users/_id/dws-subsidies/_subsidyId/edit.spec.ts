/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsSubsidyStateKey, DwsSubsidyStore, dwsSubsidyStoreKey } from '~/composables/stores/use-dws-subsidy-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsSubsidyEditPage from '~/pages/users/_id/dws-subsidies/_subsidyId/edit.vue'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsSubsidyResponseStub } from '~~/stubs/create-dws-subsidy-response-stub'
import { createDwsSubsidyStoreStub } from '~~/stubs/create-dws-subsidy-store-stub'
import { createDwsSubsidyStub } from '~~/stubs/create-dws-subsidy-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/dws-projects/_projectId/edit.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
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
    copayRate: undefined as any,
    benefitAmount: undefined as any,
    copayAmount: undefined as any,
    note: '鶏白湯に塩だった。もやしはメンマと、醤油がタンメンは背脂を注文する。魚粉が醤油を注文する。野菜が好きだ。担々麺にしよう。わかめに雲呑も好きだ。こってりの替え玉がタマネギマシマシで。'
  }
  const mocks = {
    $router,
    $back,
    $form,
    $snackbar
  }
  const stub = createDwsSubsidyStub()
  const dwsSubsidyStore = createDwsSubsidyStoreStub(createDwsSubsidyResponseStub(stub.id))
  const userResponse = createUserResponseStub(stub.userId)
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    isShallow?: true
    store?: DwsSubsidyStore
  }

  function mountComponent ({ isShallow, store: s }: MountComponentParams = {}) {
    const store = s ?? dwsSubsidyStore
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(DwsSubsidyEditPage, {
      ...provides(
        [dwsSubsidyStoreKey, store],
        [dwsSubsidyStateKey, store.state],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('submit', () => {
    const stub = createDwsSubsidyStub()
    const { id, userId } = stub
    const dwsSubsidyStore = createDwsSubsidyStoreStub(createDwsSubsidyResponseStub(id))

    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn(dwsSubsidyStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked(dwsSubsidyStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked(dwsSubsidyStore.update).mockClear()
    })

    it('should call dwsSubsidyStore.update when pass the validation and subsidyType is benefitRate', async () => {
      const expected = {
        form: {
          ...form,
          copayRate: 0,
          benefitAmount: 0,
          copayAmount: 0
        },
        id,
        userId
      }
      mountComponent({ isShallow: true, store: dwsSubsidyStore })

      await wrapper.vm.submit(form)

      expect(dwsSubsidyStore.update).toHaveBeenCalledTimes(1)
      expect(dwsSubsidyStore.update).toHaveBeenCalledWith(expected)
      unmountComponent()
    })

    it('should call dwsSubsidyStore.update when pass the validation and subsidyType is copayRate', async () => {
      const submitForm = {
        ...form,
        subsidyType: UserDwsSubsidyType.copayRate,
        benefitRate: undefined,
        copayRate: 20,
        benefitAmount: undefined,
        copayAmount: undefined
      }
      const expected = {
        form: {
          ...submitForm,
          benefitRate: 0,
          benefitAmount: 0,
          copayAmount: 0
        },
        id,
        userId
      }
      mountComponent({ isShallow: true, store: dwsSubsidyStore })

      await wrapper.vm.submit(submitForm)

      expect(dwsSubsidyStore.update).toHaveBeenCalledTimes(1)
      expect(dwsSubsidyStore.update).toHaveBeenCalledWith(expected)
      unmountComponent()
    })

    it('should call dwsSubsidyStore.update when pass the validation and subsidyType is benefitAmount', async () => {
      const submitForm = {
        ...form,
        subsidyType: UserDwsSubsidyType.benefitAmount,
        benefitRate: undefined,
        copayRate: undefined,
        benefitAmount: 1311,
        copayAmount: undefined
      }
      const expected = {
        form: {
          ...submitForm,
          benefitRate: 0,
          copayRate: 0,
          copayAmount: 0
        },
        id,
        userId
      }
      mountComponent({ isShallow: true, store: dwsSubsidyStore })

      await wrapper.vm.submit(submitForm)

      expect(dwsSubsidyStore.update).toHaveBeenCalledTimes(1)
      expect(dwsSubsidyStore.update).toHaveBeenCalledWith(expected)
      unmountComponent()
    })

    it('should call dwsSubsidyStore.update when pass the validation and subsidyType is copay', async () => {
      const submitForm = {
        ...form,
        subsidyType: UserDwsSubsidyType.copayAmount,
        benefitRate: undefined,
        copayRate: undefined,
        benefitAmount: undefined,
        copayAmount: 1311
      }
      const expected = {
        form: {
          ...submitForm,
          benefitRate: 0,
          copayRate: 0,
          benefitAmount: 0
        },
        id,
        userId
      }
      mountComponent({ isShallow: true, store: dwsSubsidyStore })

      await wrapper.vm.submit(submitForm)

      expect(dwsSubsidyStore.update).toHaveBeenCalledTimes(1)
      expect(dwsSubsidyStore.update).toHaveBeenCalledWith(expected)
      unmountComponent()
    })

    it('should display message when succeeded', async () => {
      mountComponent({ isShallow: true, store: dwsSubsidyStore })

      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('自治体助成情報を編集しました。')
      unmountComponent()
    })

    it.each([
      ['periodStart', '適用期間（開始）を入力してください。', UserDwsSubsidyType.benefitRate],
      ['periodEnd', '適用期間（終了）を入力してください。', UserDwsSubsidyType.benefitRate],
      ['cityName', '助成自治体名を入力してください。', UserDwsSubsidyType.benefitRate],
      ['cityCode', '助成自治体番号を入力してください。', UserDwsSubsidyType.benefitRate],
      ['subsidyType', '給付方式を入力してください。', UserDwsSubsidyType.benefitRate],
      ['benefitRate', '給付率を入力してください。', UserDwsSubsidyType.benefitRate],
      ['copayRate', '本人負担率を入力してください。', UserDwsSubsidyType.copayRate],
      ['benefitAmount', '給付額を入力してください。', UserDwsSubsidyType.benefitAmount],
      ['copayAmount', '本人負担額を入力してください。', UserDwsSubsidyType.copayAmount]
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, subsidyType) => {
        const { dwsSubsidy } = createDwsSubsidyResponseStub(stub.id)
        const dwsSubsidyStore = createDwsSubsidyStoreStub({
          dwsSubsidy: { ...dwsSubsidy, subsidyType }
        })
        jest.spyOn(dwsSubsidyStore, 'update').mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))
        mountComponent({ store: dwsSubsidyStore })

        await wrapper.vm.submit(form)
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)
        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
        unmountComponent()
      }
    )
  })
})
