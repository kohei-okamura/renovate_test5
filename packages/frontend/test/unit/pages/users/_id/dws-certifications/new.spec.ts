/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { DwsCertificationAgreementType } from '@zinger/enums/lib/dws-certification-agreement-type'
import { DwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsLevel } from '@zinger/enums/lib/dws-level'
import { DwsType } from '@zinger/enums/lib/dws-type'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsCertificationNewPage from '~/pages/users/_id/dws-certifications/new.vue'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
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

describe('pages/users/_id/dws-certifications/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('dwsCertifications')
  const $back = createMockedBack()
  const $route = createMockedRoute({ params: { id: '2' } })
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: DwsCertificationsApi.Form = {
    child: {
      name: {
        familyName: '倉田',
        givenName: '綾',
        displayName: '倉田 綾',
        phoneticFamilyName: 'クラタ',
        phoneticGivenName: 'アヤ',
        phoneticDisplayName: 'クラタ アヤ'
      },
      birthday: '1988-08-23'
    },
    effectivatedOn: '1995/01/20',
    status: DwsCertificationStatus.applied,
    dwsNumber: '0123456789',
    dwsTypes: [DwsType.physical],
    issuedOn: '1995/01/20',
    cityName: '東伯郡琴浦町',
    cityCode: '34033',
    dwsLevel: DwsLevel.level1,
    isSubjectOfComprehensiveSupport: true,
    activatedOn: '1995/01/20',
    deactivatedOn: '1995/01/20',
    grants: [
      {
        dwsCertificationServiceType: DwsCertificationServiceType.physicalCare,
        grantedAmount: 'amount',
        activatedOn: '1995/01/20',
        deactivatedOn: '1995/01/20'
      }
    ],
    copayLimit: 6894,
    copayActivatedOn: '1995/01/20',
    copayDeactivatedOn: '1995/01/20',
    copayCoordination: {
      copayCoordinationType: 3,
      officeId: 2
    },
    agreements: [
      {
        indexNumber: 2,
        officeId: 3,
        dwsCertificationAgreementType: DwsCertificationAgreementType.accompany,
        paymentAmount: 44520,
        agreedOn: '1995/01/20',
        expiredOn: '1995/01/20'
      }
    ]
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
  const userStore = createUserStoreStub(createUserResponseStub(userId))

  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    wrapper = mount(DwsCertificationNewPage, {
      ...provides(
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
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeEach(() => {
      jest.spyOn($api.dwsCertifications, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked($api.dwsCertifications.create).mockReset()
    })

    it('should call $api.dwsCertifications.create when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.dwsCertifications.create).toHaveBeenCalledTimes(1)
      expect($api.dwsCertifications.create).toHaveBeenCalledWith({ form, userId })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('障害福祉サービス受給者証を登録しました。')
    })

    it.each([
      ['childFamilyName', '姓を入力してください。', 'family-name'],
      ['childGivenName', '名を入力してください。', 'given-name'],
      ['childPhoneticFamilyName', 'フリガナ：姓を入力してください。', 'phonetic-family-name'],
      ['childPhoneticGivenName', 'フリガナ：名を入力してください。', 'phonetic-given-name'],
      ['childBirthday', '生年月日を入力してください。', 'birthday'],
      ['effectivatedOn', '適用日を入力してください。'],
      ['status', '認定区分を入力してください。'],
      ['dwsNumber', '受給者証番号を入力してください。'],
      ['dwsTypes', '障害種別を入力してください。'],
      ['issuedOn', '交付年月日を入力してください。'],
      ['cityName', '市町村名を入力してください。'],
      ['cityCode', '市町村番号を入力してください。'],
      ['dwsLevel', '障害支援区分を入力してください。'],
      ['activatedOn', '認定有効期間（開始）を入力してください。'],
      ['deactivatedOn', '認定有効期間（終了）を入力してください。'],
      // @TODO 初期表示時点では介護給付費の支給決定内容は「障害支援区分」「認定有効期間」以外は非表示ため一旦テスト対象から除外する
      // ['dwsCertificationServiceType', 'サービス種別を入力してください。'],
      // ['grantActivatedOn', '支給決定期間（開始）を入力してください。'],
      // ['grantDeactivatedOn', '支給決定期間（終了）を入力してください。'],
      // ['grantedAmount', '支給量等を入力してください。'],
      ['copayLimit', '負担上限月額を入力してください。'],
      ['copayActivatedOn', '利用者負担適用期間（開始）を入力してください。'],
      ['copayDeactivatedOn', '利用者負担適用期間（終了）を入力してください。'],
      ['copayCoordinationType', '上限管理区分を入力してください。']
      // @TODO 上限額管理事業所名は初期表示時点では非表示のため一旦テスト対象から除外する
      // ['copayOfficeId', '上限額管理事業所名を入力してください。'],
      // @TODO 訪問系サービス事業者記入欄（以下6項目）は初期表示時点では非表示のため一旦テスト対象から除外する
      // ['indexNumber', '番号を入力してください。'],
      // ['officeId', '事業所を入力してください。'],
      // ['dwsCertificationAgreementType', 'サービス内容を入力してください。'],
      // ['paymentAmount', '契約支給量を入力してください。'],
      // ['agreedOn', '契約日を入力してください。'],
      // ['expiredOn', '当該契約支給量によるサービス提供終了日を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        jest.spyOn($api.dwsCertifications, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

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
})
