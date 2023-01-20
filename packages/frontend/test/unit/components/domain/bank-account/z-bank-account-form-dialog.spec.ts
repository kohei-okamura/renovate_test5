/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZBankAccountFormDialog from '~/components/domain/bank-account/z-bank-account-form-dialog.vue'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-bank-account-form-dialog.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const form: BankAccountsApi.Form = {
    bankName: '三菱UFJ',
    bankCode: '0005',
    bankBranchName: '四日市中央',
    bankBranchCode: '450',
    bankAccountType: 2,
    bankAccountNumber: '0221910',
    bankAccountHolder: 'イタクラ ハルカ'
  }
  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZBankAccountFormDialog, {
      mocks,
      propsData: {
        dialog: true,
        errors: {},
        progress: false,
        value: { ...form }
      },
      ...options
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: BankAccountsApi.Form = {}) {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    async function remountAndObserverValidate (values: BankAccountsApi.Form = {}) {
      unmountComponent()
      mountComponent({
        propsData: {
          dialog: true,
          errors: {},
          progress: false,
          value: {
            ...form,
            ...values
          }
        }
      })
      observer = getValidationObserver(wrapper)
      await observer.validate()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should fail when bankName is empty', async () => {
      await validate({
        bankName: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when bankName is longer than 100', async () => {
      await validate({
        bankName: '三'.repeat(100)
      })
      expect(observer).toBePassed()

      await validate({
        bankName: '三'.repeat(101)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-name] .v-messages').text()).toBe('100文字以内で入力してください。')
    })

    it('should fail when bankCode is empty', async () => {
      await validate({
        bankCode: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-code] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when bankCode is other than 4 digits', async () => {
      await validate({
        bankCode: '1'.repeat(4)
      })
      expect(observer).toBePassed()

      await validate({
        bankCode: '1'.repeat(5)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-code] .v-messages').text()).toBe('4桁の半角数字で入力してください。')
    })

    it('should fail when bankBranchName is empty', async () => {
      await validate({
        bankBranchName: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-branch-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when bankBranchName is longer than 100', async () => {
      await validate({
        bankBranchName: '四'.repeat(100)
      })
      expect(observer).toBePassed()

      await validate({
        bankBranchName: '四'.repeat(101)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-branch-name] .v-messages').text()).toBe('100文字以内で入力してください。')
    })

    it('should fail when bankBranchCode is empty', async () => {
      await validate({
        bankBranchCode: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-branch-code] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when bankCode is other than 3 digits', async () => {
      await validate({
        bankBranchCode: '2'.repeat(3)
      })
      expect(observer).toBePassed()

      await validate({
        bankBranchCode: '2'.repeat(4)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-branch-code] .v-messages').text()).toBe('3桁の半角数字で入力してください。')
    })

    it('should fail when bankAccountType is empty', async () => {
      await validate({
        bankAccountType: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-account-type] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when bankAccountNumber is empty', async () => {
      await validate({
        bankAccountNumber: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-account-number] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when not japan post bank and bankAccountNumber is not 7 digit', async () => {
      await validate({
        bankAccountNumber: '1'.repeat(7)
      })
      expect(observer).toBePassed()

      await validate({
        bankAccountNumber: '1'.repeat(6)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-account-number] .v-messages').text()).toBe('7文字で入力してください。')
    })

    it('should fail when japan post bank and bankAccountNumber is not 8 digit', async () => {
      await remountAndObserverValidate({
        ...form,
        bankCode: '9900'
      })

      await validate({
        bankCode: '9900',
        bankAccountNumber: '1'.repeat(8)
      })
      expect(observer).toBePassed()

      await validate({
        bankCode: '9900',
        bankAccountNumber: '1'.repeat(7)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-account-number] .v-messages').text()).toBe('8文字で入力してください。')
    })

    it('should fail when japan post bank and bankAccountNumber is not end with 1', async () => {
      await validate({
        bankCode: '9900',
        bankAccountNumber: '1'.repeat(8)
      })
      expect(observer).toBePassed()

      await validate({
        bankCode: '9900',
        bankAccountNumber: '2'.repeat(8)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-account-number] .v-messages').text()).toBe('末尾に1を入力してください。')
    })

    it('should fail when bankAccountHolder is empty', async () => {
      await validate({
        bankAccountHolder: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-account-holder] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when bankAccountHolder is longer than 200', async () => {
      await validate({
        bankAccountHolder: 'ヨ'.repeat(200)
      })
      expect(observer).toBePassed()

      await validate({
        bankAccountHolder: 'ヨ'.repeat(201)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-account-holder] .v-messages').text()).toBe('200文字以内で入力してください。')
    })

    it('should fail when bankAccountHolder is not zengin data record characters', async () => {
      await validate({
        bankAccountHolder: '0０aAＡＺアｱァ 　(）「｣.．-¥￥'
      })
      expect(observer).toBePassed()

      await validate({
        bankAccountHolder: '[]{}'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-bank-account-holder] .v-messages').text()).toBe('口座名義に使用できない文字が含まれています。口座名義に間違いがないかご確認ください。')
    })
  })

  describe('event', () => {
    beforeEach(() => {
      mountComponent()
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should emit submit when ok button clicked', async () => {
      const button = wrapper.find('[data-ok]')
      const eventName = 'submit'
      await click(() => button)
      expect(wrapper.emitted(eventName)).toBeTruthy()
      expect(wrapper.emitted(eventName)!.length).toBe(1)
      expect(wrapper.emitted(eventName)![0][0]).toStrictEqual(form)
    })
  })

  describe('autoKana', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should update phoneticFamilyName when it blur', () => {
      const input = wrapper.find('[data-bank-account-holder-input]')
      input.setValue('たまい　しおり')
      input.trigger('blur')

      expect(wrapper.vm.form.bankAccountHolder).toBe('タマイ　シオリ')
    })
  })
})
