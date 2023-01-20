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
import ProfileEditPage from '~/pages/profile/edit.vue'
import { StaffsApi } from '~/services/api/staffs-api'
import { $datetime } from '~/services/datetime-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { createStaffStoreStub } from '~~/stubs/create-staff-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
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

describe('pages/profile/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const dummy = createStaffStub()
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const staffStore = createStaffStoreStub(createStaffResponseStub())

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ProfileEditPage, {
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

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useOfficeGroups).mockReturnValue(createUseOfficeGroupsStub())
    mocked(useRoles).mockReturnValue(createUseRolesStub())
  })

  afterAll(() => {
    mocked(useRoles).mockReset()
    mocked(useOfficeGroups).mockReset()
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
      status: StaffStatus.active
    }

    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn(staffStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked(staffStore.update).mockReset()
    })

    it('should call staffStore.update when pass the validation', async () => {
      await wrapper.vm.$data.submit(form)

      expect(staffStore.update).toHaveBeenCalledTimes(1)
      expect(staffStore.update).toHaveBeenCalledWith({ form, id: dummy.id })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.$data.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('登録情報を更新しました。')
    })

    it.each([
      // @TODO 社員番号、状態、名前、名前：フリガナ、性別、生年月日、資格、所属事業所、ロール は編集できないため、テスト対象外
      ['postcode', '郵便番号を入力してください。'],
      ['prefecture', '都道府県を入力してください。'],
      ['city', '市区町村を入力してください。'],
      ['street', '町名・番地を入力してください。'],
      ['apartment', '建物名などを入力してください。'],
      ['tel', '電話番号を入力してください。'],
      ['fax', 'FAX番号を入力してください。'],
      ['email', 'メールアドレスを入力してください。']
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

        await wrapper.vm.$data.submit(form)
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
