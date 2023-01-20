/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Purpose } from '@zinger/enums/lib/purpose'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsAreaGradesStateKey, dwsAreaGradesStoreKey } from '~/composables/stores/use-dws-area-grades-store'
import { ltcsAreaGradesStateKey, ltcsAreaGradesStoreKey } from '~/composables/stores/use-ltcs-area-grades-store'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { HttpStatusCode } from '~/models/http-status-code'
import OfficesNewPage from '~/pages/offices/new.vue'
import { OfficesApi } from '~/services/api/offices-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsAreaGradeStubs } from '~~/stubs/create-dws-area-grade-stub'
import { createDwsAreaGradesStoreStub } from '~~/stubs/create-dws-area-grades-store-stub'
import { createLtcsAreaGradeStubs } from '~~/stubs/create-ltcs-area-grade-stub'
import { createLtcsAreaGradesStoreStub } from '~~/stubs/create-ltcs-area-grades-store-stub'
import { OFFICE_GROUP_IDS } from '~~/stubs/create-office-group-stub'
import { createUseOfficeGroupsStub } from '~~/stubs/create-use-office-groups-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-office-groups')

describe('pages/offices/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('offices')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: OfficesApi.Form = {
    purpose: Purpose.internal,
    name: '土屋訪問介護事業所 中野坂上',
    abbr: '中野坂上',
    phoneticName: 'ツチヤホウモンカイゴジギョウショナカノサカウエ',
    corporationName: '',
    phoneticCorporationName: '',
    postcode: '123-4567',
    prefecture: Prefecture.tokyo,
    city: '中野区',
    street: '中央1-2-3',
    apartment: '',
    tel: '03-1111-1111',
    fax: '',
    email: 'smith@example.jp',
    qualifications: [],
    officeGroupId: OFFICE_GROUP_IDS[0],
    dwsGenericService: {},
    dwsCommAccompanyService: {},
    ltcsHomeVisitLongTermCareService: {},
    ltcsCareManagementService: {},
    ltcsCompHomeVisitingService: {},
    status: undefined
  }
  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }
  const dwsAreaGradesStore = createDwsAreaGradesStoreStub({
    dwsAreaGrades: createDwsAreaGradeStubs()
  })
  const ltcsAreaGradesStore = createLtcsAreaGradesStoreStub({
    ltcsAreaGrades: createLtcsAreaGradeStubs()
  })

  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    wrapper = mount(OfficesNewPage, {
      ...provides(
        [dwsAreaGradesStoreKey, dwsAreaGradesStore],
        [dwsAreaGradesStateKey, dwsAreaGradesStore.state],
        [ltcsAreaGradesStoreKey, ltcsAreaGradesStore],
        [ltcsAreaGradesStateKey, ltcsAreaGradesStore.state]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOfficeGroups).mockReturnValue(createUseOfficeGroupsStub())
  })

  afterAll(() => {
    mocked(useOfficeGroups).mockReset()
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
      jest.spyOn($api.offices, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked($api.offices.create).mockReset()
    })

    it('should call $api.offices.create when pass the validation', async () => {
      await wrapper.vm.submit(form)
      expect($api.offices.create).toHaveBeenCalledTimes(1)
      expect($api.offices.create).toHaveBeenCalledWith({ form })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('事業所情報を登録しました。')
    })

    it.each([
      ['purpose', '事業所区分を入力してください。'],
      ['name', '事業所名を入力してください。'],
      ['phoneticName', '事業所名：フリガナを入力してください。'],
      ['abbr', '事業所名：略称を入力してください。'],
      // TODO 事業所グループは初期表示時点では非表示のためテスト対象から除外する
      // ['officeGroupId', '事業所グループを入力してください。'],
      ['status', '状態をを入力してください。'],
      ['postcode', '郵便番号を入力してください。'],
      ['prefecture', '都道府県を入力してください。'],
      ['city', '市区町村を入力してください。'],
      ['street', '町名・番地を入力してください。'],
      ['tel', '電話番号を入力してください。']
      // TODO 障害福祉サービス、介護保険サービスは初期表示時点では非表示のためテスト対象から除外する
      // ['dwsGenericService.code', '事業所番号を入力してください。'],
      // ['dwsGenericService.openedOn', '開設日を入力してください。'],
      // ['dwsGenericService.designationExpiredOn', '指定更新期日を入力してください。'],
      // ['dwsGenericService.dwsAreaGradeId', '地域区分を入力してください。'],
      // ['dwsGenericService.code', '事業所番号を入力してください。'],
      // ['dwsCommAccompanyService.openedOn', '開設日を入力してください。'],
      // ['dwsCommAccompanyService.designationExpiredOn', '指定更新期日を入力してください。'],
      // ['ltcsHomeVisitLongTermCareService.code', '事業所番号を入力してください。'],
      // ['ltcsHomeVisitLongTermCareService.openedOn', '開設日を入力してください。'],
      // ['ltcsHomeVisitLongTermCareService.designationExpiredOn', '指定更新期日を入力してください。'],
      // ['ltcsHomeVisitLongTermCareService.ltcsAreaGradeId', '地域区分を入力してください。'],
      // ['ltcsCareManagementService.code', '事業所番号を入力してください。'],
      // ['ltcsCareManagementService.openedOn', '開設日を入力してください。'],
      // ['ltcsCareManagementService.designationExpiredOn', '指定更新期日を入力してください。'],
      // ['ltcsCareManagementService.ltcsAreaGradeId', '地域区分を入力してください。'],
      // ['ltcsCompHomeVisitingService.code', '事業所番号を入力してください。'],
      // ['ltcsCompHomeVisitingService.openedOn', '開設日を入力してください。'],
      // ['ltcsCompHomeVisitingService.designationExpiredOn', '指定更新期日を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked($api.offices.create).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect($snackbar.success).not.toHaveBeenCalled()
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
