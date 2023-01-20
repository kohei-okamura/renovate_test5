/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { LtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsInsCardsNewPage from '~/pages/users/_id/ltcs-ins-cards/new.vue'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/users/_id/ltcs-ins-cards/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsInsCards', 'users')
  const $back = createMockedBack()
  const $route = createMockedRoute({ params: { id: '2' } })
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: LtcsInsCardsApi.Form = {
    effectivatedOn: '2020/01/20',
    status: LtcsInsCardStatus.applied,
    insNumber: '2304316218',
    issuedOn: '2020/01/21',
    insurerNumber: '24009439',
    insurerName: '邑楽郡明和町',
    ltcsLevel: LtcsLevel.careLevel2,
    certificatedOn: '2020/01/22',
    activatedOn: '2020/01/23',
    deactivatedOn: '2020/01/24',
    maxBenefitQuotas: [
      {
        ltcsInsCardServiceType: LtcsInsCardServiceType.serviceType2,
        maxBenefitQuota: 280600
      }
    ],
    carePlanAuthorOfficeId: 2,
    copayRate: 30,
    copayActivatedOn: '2020/01/25',
    copayDeactivatedOn: '2020/01/26'
  }
  const mocks = {
    $api,
    $back,
    $form,
    $route,
    $router,
    $snackbar
  }
  const userId = 1
  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    const userResponse = createUserResponseStub(userId)
    const userStore = createUserStoreStub(userResponse)
    wrapper = mount(LtcsInsCardsNewPage, () => ({
      ...provides(
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      ),
      mocks
    }))
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
      jest.spyOn($api.ltcsInsCards, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($api.ltcsInsCards.create).mockReset()
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
    })

    it('should call $api.ltcsInsCards.create when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.ltcsInsCards.create).toHaveBeenCalledTimes(1)
      expect($api.ltcsInsCards.create).toHaveBeenCalledWith({ form, userId })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('介護保険被保険者証を登録しました。')
    })

    it.each([
      ['effectivatedOn', '適用日を入力してください。'],
      ['insNumber', '被保険者証番号を入力してください。'],
      ['status', '認定区分を入力してください。', 'ltcs-ins-card-status'],
      ['issuedOn', '交付年月日を入力してください。'],
      ['insurerName', '保険者の名称を入力してください。'],
      ['insurerNumber', '保険者番号を入力してください。'],
      ['ltcsLevel', '要介護状態区分等を入力してください。'],
      ['certificatedOn', '認定年月日を入力してください。'],
      ['activatedOn', '認定の有効期間（開始）を入力してください。'],
      ['deactivatedOn', '認定の有効期間（終了）を入力してください。'],
      // @TODO 種類支給限度基準額（以下2項目）は初期表示時点では非表示のため一旦テスト対象から除外する
      // ['ltcsInsCardServiceType', 'サービスの種類を入力してください。'],
      // ['maxBenefitQuota', '種類支給限度基準額を入力してください。'],
      ['copayRate', '利用者負担の割合を入力してください。'],
      ['copayActivatedOn', '利用者負担適用期間（開始）を入力してください。'],
      ['copayDeactivatedOn', '利用者負担適用期間（終了）を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        jest.spyOn($api.ltcsInsCards, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
      }
    )
  })
})
