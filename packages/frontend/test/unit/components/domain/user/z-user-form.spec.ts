/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { BillingDestination } from '@zinger/enums/lib/billing-destination'
import { ContactRelationship } from '@zinger/enums/lib/contact-relationship'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import Vue from 'vue'
import ZUserForm from '~/components/domain/user/z-user-form.vue'
import { UsersApi } from '~/services/api/users-api'
import { $datetime } from '~/services/datetime-service'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-user-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const { billingDestination: billings } = createUserStub()
  const mocks = {
    $form
  }
  const propsData = {
    errors: {},
    buttonText: '登録',
    progress: false,
    value: {}
  }
  let wrapper: Wrapper<Vue & any>
  const form: UsersApi.Form = {
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
    contacts: [{ tel: '03-5937-6825', relationship: 20, name: '玉井 詩織' }],
    isEnabled: false,
    billingDestination: {
      destination: BillingDestination.agent,
      paymentMethod: PaymentMethod.withdrawal,
      contractNumber: '0943992350',
      corporationName: 'デイサービス土屋 中野中央',
      agentName: '新井 恵梨香',
      addr: {
        postcode: '545-0034',
        prefecture: 27,
        city: '大阪市阿倍野区',
        street: '阿倍野元町2-6-12',
        apartment: ''
      },
      tel: '0731-85-3606'
    }
  }

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZUserForm, {
      ...options,
      mocks,
      propsData
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

  it('should match correct emit submit object', async () => {
    mountComponent()
    await setData(wrapper, { form })
    await wrapper.vm.submit()
    expect(wrapper.emitted().submit?.[0][0]).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: DeepPartial<UsersApi.Form> = {}) {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
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

    it('should fail when familyName is empty', async () => {
      await validate({
        familyName: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-family-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when familyName is longer than 100', async () => {
      await validate({
        familyName: '山'.repeat(100)
      })
      expect(observer).toBePassed()

      await validate({
        familyName: '山'.repeat(101)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-family-name] .v-messages').text()).toBe('100文字以内で入力してください。')
    })

    it('should fail when givenName is empty', async () => {
      await validate({
        givenName: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-given-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when givenName is longer than 100', async () => {
      await validate({
        givenName: '山'.repeat(100)
      })
      expect(observer).toBePassed()

      await validate({
        givenName: '山'.repeat(101)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-given-name] .v-messages').text()).toBe('100文字以内で入力してください。')
    })

    it('should fail when phoneticFamilyName is empty', async () => {
      await validate({
        phoneticFamilyName: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-phonetic-family-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when phoneticFamilyName contains non-katakana character(s)', async () => {
      await validate({
        phoneticFamilyName: 'アキラ'
      })
      expect(observer).toBePassed()

      await validate({
        phoneticFamilyName: '川上アキラ'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-phonetic-family-name] .v-messages').text()).toBe('カタカナで入力してください。')
    })

    it('should fail when phoneticFamilyName is longer than 100', async () => {
      await validate({
        phoneticFamilyName: 'ア'.repeat(100)
      })
      expect(observer).toBePassed()

      await validate({
        phoneticFamilyName: 'ア'.repeat(101)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-phonetic-family-name] .v-messages').text()).toBe('100文字以内で入力してください。')
    })

    it('should fail when phoneticGivenName is empty', async () => {
      await validate({
        phoneticGivenName: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-phonetic-given-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when phoneticGivenName contains non-katakana character(s)', async () => {
      await validate({
        phoneticGivenName: 'アキラ'
      })
      expect(observer).toBePassed()

      await validate({
        phoneticGivenName: '川上アキラ'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-phonetic-given-name] .v-messages').text()).toBe('カタカナで入力してください。')
    })

    it('should fail when phoneticGivenName is longer than 100', async () => {
      await validate({
        phoneticGivenName: 'ア'.repeat(100)
      })
      expect(observer).toBePassed()

      await validate({
        phoneticGivenName: 'ア'.repeat(101)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-phonetic-given-name] .v-messages').text()).toBe('100文字以内で入力してください。')
    })

    it('should fail when sex is empty', async () => {
      await validate({
        sex: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-sex] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when birthday is empty', async () => {
      await validate({
        birthday: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-birthday] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when postcode is empty', async () => {
      await validate({
        postcode: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-postcode] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when postcode is not a valid postcode', async () => {
      await validate({
        postcode: '123'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-postcode] .v-messages').text()).toBe('郵便番号は7桁で入力してください。')
    })

    it('should fail when prefecture is empty', async () => {
      await validate({
        prefecture: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-prefecture] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when city is empty', async () => {
      await validate({
        city: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-city] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when city is longer than 200', async () => {
      await validate({
        city: '川'.repeat(200)
      })
      expect(observer).toBePassed()

      await validate({
        city: '川'.repeat(201)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-city] .v-messages').text()).toBe('200文字以内で入力してください。')
    })

    it('should fail when street is empty', async () => {
      await validate({
        street: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-street] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when street is longer than 200', async () => {
      await validate({
        street: '川'.repeat(200)
      })
      expect(observer).toBePassed()

      await validate({
        street: '川'.repeat(201)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-street] .v-messages').text()).toBe('200文字以内で入力してください。')
    })

    it('should not fail even if apartment is empty', async () => {
      await validate({
        apartment: ''
      })
      expect(observer).toBePassed()
    })

    it('should fail when apartment is longer than 200', async () => {
      await validate({
        apartment: '川'.repeat(200)
      })
      expect(observer).toBePassed()

      await validate({
        apartment: '川'.repeat(201)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-apartment] .v-messages').text()).toBe('200文字以内で入力してください。')
    })

    it('should fail when tel is empty', async () => {
      await validate({
        contacts: [{ ...form.contacts![0], tel: '' }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tel] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when tel is not a valid phone number', async () => {
      await validate({
        contacts: [{ ...form.contacts![0], tel: '0-123-4567-89' }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tel] .v-messages').text()).toBe('有効な電話番号を入力してください。')
    })

    it('should fail relationship is empty', async () => {
      await validate({
        contacts: [{ ...form.contacts![0], relationship: undefined }]
      })
      expect(observer).not.toBePassed()
    })

    it('should fail when name is empty when relationship is not theirself', async () => {
      await validate({
        contacts: [{ ...form.contacts![0], relationship: ContactRelationship.family, name: undefined }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should not fail when name is empty with relationship is theirself', async () => {
      await validate({
        contacts: [{ ...form.contacts![0], relationship: ContactRelationship.theirself, name: undefined }]
      })
      expect(observer).toBePassed()
    })

    it('should not fail when billingDestination is theirself and paymentMethod is not withdrawal and empty anothor property', async () => {
      await validate({
        billingDestination: {
          destination: BillingDestination.theirself,
          paymentMethod: PaymentMethod.transfer
        }
      })
      expect(observer).toBePassed()
    })

    it('should not fail when billingDestination is theirself and paymentMethod is not withdrawal and empty anothor property', async () => {
      await validate({
        billingDestination: {
          destination: BillingDestination.theirself,
          paymentMethod: PaymentMethod.transfer
        }
      })
      expect(observer).toBePassed()
    })

    it('should fail when contractNumber is empty when paymentMethod is not withdrawal', async () => {
      await validate({
        billingDestination: {
          destination: BillingDestination.theirself,
          paymentMethod: PaymentMethod.withdrawal,
          contractNumber: ''
        }
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-contract-number] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when contractNumber is not 10 digit when paymentMethod is not withdrawal', async () => {
      await validate({
        billingDestination: {
          destination: BillingDestination.theirself,
          paymentMethod: PaymentMethod.withdrawal,
          contractNumber: '123456789'
        }
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-contract-number] .v-messages').text()).toBe('10文字で入力してください。')
    })

    it('should not fail when billingDestination is agent and paymentMethod is not withdrawal', async () => {
      await validate({
        billingDestination: {
          destination: BillingDestination.agent,
          paymentMethod: PaymentMethod.withdrawal,
          contractNumber: billings.contractNumber,
          agentName: billings.agentName,
          addr: billings.addr,
          tel: billings.tel
        }
      })
      expect(observer).toBePassed()
    })

    it('should fail when billingDestination is agent and paymentMethod is not withdrawal and agentName is empty', async () => {
      await validate({
        billingDestination: {
          destination: BillingDestination.agent,
          paymentMethod: PaymentMethod.withdrawal,
          contractNumber: billings.contractNumber,
          agentName: '',
          addr: billings.addr,
          tel: billings.tel
        }
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-agent-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should not fail when billingCorporation is agent and paymentMethod is not withdrawal and fill anothor property', async () => {
      await validate({
        billingDestination: {
          ...billings,
          destination: BillingDestination.corporation,
          paymentMethod: PaymentMethod.withdrawal
        }
      })
      expect(observer).toBePassed()
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
      const input = wrapper.find('[data-phonetic-family-name-input]')
      input.setValue('たまい')
      input.trigger('blur')

      expect(wrapper.vm.form.phoneticFamilyName).toBe('タマイ')
    })

    it('should update phoneticGivenName when it blur', () => {
      const input = wrapper.find('[data-phonetic-given-name-input]')
      input.setValue('しおり')
      input.trigger('blur')

      expect(wrapper.vm.form.phoneticGivenName).toBe('シオリ')
    })
  })

  describe('formatPhoneNumber', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should format tel', () => {
      const input = wrapper.find('[data-tel-input]')
      input.setValue('0359376825')
      input.trigger('blur')

      expect(wrapper.vm.form.contacts[0].tel).toBe('03-5937-6825')
    })
  })
})
