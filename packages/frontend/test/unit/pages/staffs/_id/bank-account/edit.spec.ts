/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { staffStateKey, staffStoreKey } from '~/composables/stores/use-staff-store'
import { HttpStatusCode } from '~/models/http-status-code'
import StaffsBankAccountEditPage from '~/pages/staffs/_id/bank-account/edit.vue'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { createStaffStoreStub } from '~~/stubs/create-staff-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/staffs/_id/bank-account/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: BankAccountsApi.Form = {
    bankName: '三菱UFJ',
    bankCode: '0005',
    bankBranchName: '四日市中央',
    bankBranchCode: '450',
    bankAccountType: 2,
    bankAccountNumber: '0221910',
    bankAccountHolder: 'イタクラ ハルカ'
  }
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const staffStore = createStaffStoreStub(createStaffResponseStub())

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(StaffsBankAccountEditPage, {
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

  beforeEach(() => {
    mountComponent()
  })

  afterEach(() => {
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    expect(wrapper.element).toMatchSnapshot()
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn(staffStore, 'updateBankAccount').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked(staffStore.updateBankAccount).mockReset()
    })

    it('should call staffStore.updateBankAccount when pass the validation', async () => {
      await wrapper.vm.$data.submit(form)

      expect(staffStore.updateBankAccount).toHaveBeenCalledTimes(1)
      expect(staffStore.updateBankAccount).toHaveBeenCalledWith({ form })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.$data.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('銀行口座情報を編集しました。')
    })

    it.each([
      ['bankName', '銀行名を入力してください。'],
      ['bankCode', '銀行コードを入力してください。'],
      ['bankBranchName', '支店名を入力してください。'],
      ['bankBranchCode', '支店コードを入力してください。'],
      ['bankAccountType', '銀行口座種別を入力してください。'],
      ['bankAccountNumber', '口座番号を入力してください。'],
      ['bankAccountHolder', '口座名義を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(staffStore.updateBankAccount)
          .mockReset()
          .mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [key]: [message]
            }
          }))

        await wrapper.vm.$data.submit(form)
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
