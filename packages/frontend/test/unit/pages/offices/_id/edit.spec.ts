/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Purpose } from '@zinger/enums/lib/purpose'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsAreaGradesStateKey, dwsAreaGradesStoreKey } from '~/composables/stores/use-dws-area-grades-store'
import { ltcsAreaGradesStateKey, ltcsAreaGradesStoreKey } from '~/composables/stores/use-ltcs-area-grades-store'
import { officeStateKey, officeStoreKey } from '~/composables/stores/use-office-store'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { HttpStatusCode } from '~/models/http-status-code'
import OfficesEditPage from '~/pages/offices/_id/edit.vue'
import { OfficesApi } from '~/services/api/offices-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsAreaGradeStubs } from '~~/stubs/create-dws-area-grade-stub'
import { createDwsAreaGradesStoreStub } from '~~/stubs/create-dws-area-grades-store-stub'
import { createLtcsAreaGradeStubs } from '~~/stubs/create-ltcs-area-grade-stub'
import { createLtcsAreaGradesStoreStub } from '~~/stubs/create-ltcs-area-grades-store-stub'
import { OFFICE_GROUP_IDS } from '~~/stubs/create-office-group-stub'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'
import { createOfficeStoreStub } from '~~/stubs/create-office-store-stub'
import { createOfficeStubs } from '~~/stubs/create-office-stub'
import { createUseOfficeGroupsStub } from '~~/stubs/create-use-office-groups-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-office-groups')

describe('pages/offices/_id/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const services = {
    qualifications: [
      OfficeQualification.dwsHomeHelpService,
      OfficeQualification.dwsCommAccompany,
      OfficeQualification.ltcsHomeVisitLongTermCare,
      OfficeQualification.ltcsCareManagement,
      OfficeQualification.ltcsCompHomeVisiting,
      OfficeQualification.ltcsPrevention
    ],
    dwsGenericService: {
      code: '10',
      openedOn: undefined,
      designationExpiredOn: undefined,
      dwsAreaGradeId: 10
    },
    dwsCommAccompanyService: {
      code: '11',
      openedOn: undefined,
      designationExpiredOn: undefined
    },
    ltcsHomeVisitLongTermCareService: {
      code: '20',
      openedOn: undefined,
      designationExpiredOn: undefined,
      ltcsAreaGradeId: 10
    },
    ltcsCareManagementService: {
      code: '21',
      openedOn: undefined,
      designationExpiredOn: undefined,
      ltcsAreaGradeId: 20
    },
    ltcsCompHomeVisitingService: {
      code: '22',
      openedOn: undefined,
      designationExpiredOn: undefined
    },
    ltcsPreventionService: {
      code: '22',
      openedOn: undefined,
      designationExpiredOn: undefined
    }
  }
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
    officeGroupId: OFFICE_GROUP_IDS[0],
    status: OfficeStatus.inPreparation,
    ...services
  }
  const mocks = {
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
  const stubs = createOfficeStubs()
  const stub = stubs.find(x => x.purpose === Purpose.internal)!
  const office = { ...stub, ...services }
  const officeStore = createOfficeStoreStub(createOfficeResponseStub(0, office))

  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    wrapper = mount(OfficesEditPage, {
      ...provides(
        [dwsAreaGradesStoreKey, dwsAreaGradesStore],
        [dwsAreaGradesStateKey, dwsAreaGradesStore.state],
        [ltcsAreaGradesStoreKey, ltcsAreaGradesStore],
        [ltcsAreaGradesStateKey, ltcsAreaGradesStore.state],
        [officeStoreKey, officeStore],
        [officeStateKey, officeStore.state]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOfficeGroups).mockReturnValue(createUseOfficeGroupsStub())
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    mocked(useOfficeGroups).mockReset()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn(officeStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked(officeStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked(officeStore.update).mockClear()
    })

    it('should call officeStore.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect(officeStore.update).toHaveBeenCalledTimes(1)
      expect(officeStore.update).toHaveBeenCalledWith({ form, id: stub.id })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('事業所情報を編集しました。')
    })

    it.each([
      ['purpose', '事業所区分を入力してください。'],
      ['name', '事業所名を入力してください。'],
      ['phoneticName', '事業所名：フリガナを入力してください。'],
      ['abbr', '事業所名：略称を入力してください。'],
      ['officeGroupId', '事業所グループを入力してください。'],
      ['status', '状態をを入力してください。'],
      ['postcode', '郵便番号を入力してください。'],
      ['prefecture', '都道府県を入力してください。'],
      ['city', '市区町村を入力してください。'],
      ['street', '町名・番地を入力してください。'],
      ['tel', '電話番号を入力してください。'],
      ['dwsGenericService.code', '事業所番号を入力してください。'],
      ['dwsGenericService.openedOn', '開設日を入力してください。'],
      ['dwsGenericService.designationExpiredOn', '指定更新期日を入力してください。'],
      ['dwsGenericService.dwsAreaGradeId', '地域区分を入力してください。'],
      ['dwsGenericService.code', '事業所番号を入力してください。'],
      ['dwsCommAccompanyService.openedOn', '開設日を入力してください。'],
      ['dwsCommAccompanyService.designationExpiredOn', '指定更新期日を入力してください。'],
      ['ltcsHomeVisitLongTermCareService.code', '事業所番号を入力してください。'],
      ['ltcsHomeVisitLongTermCareService.openedOn', '開設日を入力してください。'],
      ['ltcsHomeVisitLongTermCareService.designationExpiredOn', '指定更新期日を入力してください。'],
      ['ltcsHomeVisitLongTermCareService.ltcsAreaGradeId', '地域区分を入力してください。'],
      ['ltcsCareManagementService.code', '事業所番号を入力してください。'],
      ['ltcsCareManagementService.openedOn', '開設日を入力してください。'],
      ['ltcsCareManagementService.designationExpiredOn', '指定更新期日を入力してください。'],
      ['ltcsCareManagementService.ltcsAreaGradeId', '地域区分を入力してください。'],
      ['ltcsCompHomeVisitingService.code', '事業所番号を入力してください。'],
      ['ltcsCompHomeVisitingService.openedOn', '開設日を入力してください。'],
      ['ltcsCompHomeVisitingService.designationExpiredOn', '指定更新期日を入力してください。'],
      ['ltcsPreventionService.code', '事業所番号を入力してください。'],
      ['ltcsPreventionService.openedOn', '開設日を入力してください。'],
      ['ltcsPreventionService.designationExpiredOn', '指定更新期日を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(officeStore.update).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
        await jest.runAllTimers()

        // foo.bar を fooBar にする
        const replacedKey = key.replace(/\.(.)/g, (_, p1) => p1.toUpperCase())
        const targetWrapper = wrapper.find(`[data-${camelToKebab(replacedKey)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
      }
    )
  })
})
