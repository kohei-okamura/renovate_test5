/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import { StaffStatus } from '@zinger/enums/lib/staff-status'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { staffStateKey, staffStoreKey } from '~/composables/stores/use-staff-store'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { useOffices } from '~/composables/use-offices'
import { useRoles } from '~/composables/use-roles'
import { HttpStatusCode } from '~/models/http-status-code'
import StaffsEditPage from '~/pages/staffs/_id/edit.vue'
import { StaffsApi } from '~/services/api/staffs-api'
import { $datetime } from '~/services/datetime-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { createStaffStoreStub } from '~~/stubs/create-staff-store-stub'
import { STAFF_ID_MIN } from '~~/stubs/create-staff-stub'
import { createUseOfficeGroupsStub } from '~~/stubs/create-use-office-groups-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseRolesStub } from '~~/stubs/create-use-roles-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-office-groups')
jest.mock('~/composables/use-roles')

describe('pages/staffs/_id/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const id = STAFF_ID_MIN
  const staffStore = createStaffStoreStub(createStaffResponseStub(id))

  let wrapper: Wrapper<Vue & any>

  async function mountComponent () {
    wrapper = await mount(StaffsEditPage, {
      ...provides(
        [staffStateKey, staffStore.state],
        [staffStoreKey, staffStore]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(async () => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useOfficeGroups).mockReturnValue(createUseOfficeGroupsStub())
    mocked(useRoles).mockReturnValue(createUseRolesStub())
    await mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    mocked(useRoles).mockReset()
    mocked(useOfficeGroups).mockReset()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    const form: StaffsApi.UpdateForm = {
      employeeNumber: '1234',
      familyName: '玉井',
      givenName: '詩織',
      phoneticFamilyName: 'タマイ',
      phoneticGivenName: 'シオリ',
      sex: Sex.female,
      birthday: $datetime.from(1995, 6, 4),
      postcode: '164-0001',
      prefecture: Prefecture.tokyo,
      city: '中野区',
      street: '中央1-35-6',
      apartment: 'レッチフィールド中野坂上6F',
      tel: '03-5937-6825',
      fax: '03-5937-6828',
      email: 'john@example.com',
      certifications: [],
      roleIds: [],
      officeIds: [],
      status: StaffStatus.retired
    }

    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn(staffStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked(staffStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked(staffStore.update).mockClear()
    })

    it('should call staffStore.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect(staffStore.update).toHaveBeenCalledTimes(1)
      expect(staffStore.update).toHaveBeenCalledWith({ form, id })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('スタッフの基本情報を編集しました。')
    })

    // TODO z-staff-edit-form.vue の詳細についてテストしているため、ここにあるべきではない
    describe('z-staff-edit-form', () => {
      it.each([
        ['employeeNumber', '社員番号を入力してください。'],
        ['familyName', '姓を入力してください。'],
        ['givenName', '名を入力してください。'],
        ['phoneticFamilyName', 'フリガナ：姓を入力してください。'],
        ['phoneticGivenName', 'フリガナ：名を入力してください。'],
        ['sex', '性別を入力してください。'],
        ['birthday', '生年月日を入力してください。'],
        ['postcode', '郵便番号を入力してください。'],
        ['prefecture', '都道府県を入力してください。'],
        ['city', '市区町村を入力してください。'],
        ['street', '町名・番地を入力してください。'],
        ['apartment', '建物名などを入力してください。'],
        ['tel', '電話番号を入力してください。'],
        ['fax', 'FAX番号を入力してください。'],
        ['email', 'メールアドレスを入力してください。'],
        ['certifications', '資格を入力してください。'],
        ['officeIds', '所属事業所を入力してください。'],
        ['roleIds', 'ロールを入力してください。'],
        ['status', '状態を入力してください。']
      ])(
        'should display errors when server responses 400 Bad Request (error occurred in "%s")',
        async (key, message) => {
          mocked(staffStore.update)
            .mockReset()
            .mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
})
