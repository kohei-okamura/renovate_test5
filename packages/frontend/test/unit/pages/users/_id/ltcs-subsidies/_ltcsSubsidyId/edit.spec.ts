/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue, { ComponentOptions } from 'vue'
import { ltcsSubsidyStateKey } from '~/composables/stores/use-ltcs-subsidy-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { HttpStatusCode } from '~/models/http-status-code'
import SubsidyEditPage from '~/pages/users/_id/ltcs-subsidies/_ltcsSubsidyId/edit.vue'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createLtcsSubsidyResponseStub } from '~~/stubs/create-ltcs-subsidy-response-stub'
import { createLtcsSubsidyStoreStub } from '~~/stubs/create-ltcs-subsidy-store-stub'
import { createLtcsSubsidyStub, LTCS_SUBSIDY_ID_MIN } from '~~/stubs/create-ltcs-subsidy-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/ltcsSubsidies/_subsidyId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsSubsidies', 'users')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: LtcsSubsidiesApi.Form = {
    period: {
      start: '1976-09-13',
      end: '1982-11-14'
    },
    defrayerCategory: 81,
    defrayerNumber: '19427456',
    recipientNumber: '54782761',
    benefitRate: 69,
    copay: 1303
  }

  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }
  const stub = createLtcsSubsidyStub(LTCS_SUBSIDY_ID_MIN)
  let wrapper: Wrapper<Vue & any>
  afterEach(() => {
    jest.clearAllMocks()
  })

  function mountComponent (options: ComponentOptions<Vue> = {}) {
    const ltcsSubsidyResponse = createLtcsSubsidyResponseStub(stub.id)
    const ltcsSubsidyStore = createLtcsSubsidyStoreStub(ltcsSubsidyResponse)
    const userResponse = createUserResponseStub(stub.userId)
    const userStore = createUserStoreStub(userResponse)
    wrapper = mount(SubsidyEditPage, {
      ...options,
      ...provides(
        [ltcsSubsidyStateKey, ltcsSubsidyStore.state],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
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
      jest.spyOn($api.ltcsSubsidies, 'update').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      jest.clearAllMocks()
    })

    it('should call $api.ltcsSubsidies.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.ltcsSubsidies.update).toHaveBeenCalledTimes(1)
      expect($api.ltcsSubsidies.update).toHaveBeenCalledWith({
        form,
        id: stub.id,
        userId: stub.userId
      })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('公費情報を編集しました。')
    })

    it.each([
      ['periodStart', '適用期間（開始）を入力してください。'],
      ['periodEnd', '適用期間（終了）を入力してください。'],
      ['defrayerCategory', '公費制度（法別番号）を入力してください。'],
      ['defrayerNumber', '負担者番号を入力してください。'],
      ['recipientNumber', '受給者番号を入力してください。'],
      ['benefitRate', '給付率を入力してください。'],
      ['copay', '本人負担額を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.ltcsSubsidies, 'update').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
