/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import { StaffStatus } from '@zinger/enums/lib/staff-status'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZStaffEditForm from '~/components/domain/staff/z-staff-edit-form.vue'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { useOffices } from '~/composables/use-offices'
import { useRoles } from '~/composables/use-roles'
import { StaffsApi } from '~/services/api/staffs-api'
import { $datetime } from '~/services/datetime-service'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createUseOfficeGroupsStub } from '~~/stubs/create-use-office-groups-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseRolesStub } from '~~/stubs/create-use-roles-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-office-groups')
jest.mock('~/composables/use-roles')

describe('z-staff-edit-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
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
    officeGroupIds: [],
    status: StaffStatus.provisional
  }
  const propsData = {
    errors: {},
    buttonText: '登録',
    progress: false,
    value: form
  }

  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZStaffEditForm, {
      ...options,
      mocks,
      propsData
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

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<StaffsApi.UpdateForm> = {}) {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent({ stubs: ['z-data-card-item', 'v-chip', 'z-keyword-filter-autocomplete'] })
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should not fail even if employeeNumber is empty', async () => {
      await validate({
        employeeNumber: ''
      })
      expect(observer).toBePassed()
    })

    it('should fail when employeeNumber is longer than 20', async () => {
      await validate({
        employeeNumber: '0'.repeat(20)
      })
      expect(observer).toBePassed()

      await validate({
        employeeNumber: '0'.repeat(21)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-employee-number] .v-messages').text()).toBe('20文字以内で入力してください。')
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
        tel: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tel] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when tel is not a valid phone number', async () => {
      await validate({
        tel: '0-123-4567-89'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tel] .v-messages').text()).toBe('有効な電話番号を入力してください。')
    })

    it('should not fail even if fax is empty', async () => {
      await validate({
        fax: ''
      })
      expect(observer).toBePassed()
    })

    it('should fail when fax is not a valid fax number', async () => {
      await validate({
        fax: '0-123-4567-89'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-fax] .v-messages').text()).toBe('有効なFAX番号を入力してください。')
    })

    it('should fail when email is empty', async () => {
      await validate({
        email: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-email] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when email is not a valid email address', async () => {
      await validate({
        email: 'this is not an email address'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-email] .v-messages').text()).toBe('有効なメールアドレスを入力してください。')
    })

    it('should fail when email is longer than 255', async () => {
      await validate({
        email: 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'
      })
      expect(observer).toBePassed()

      await validate({
        email: 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-email] .v-messages').text()).toBe('255文字以内で入力してください。')
    })

    it('should fail when status is undefined', async () => {
      await validate({
        status: undefined
      })
      expect(observer).not.toBePassed()
    })
  })

  describe('autoKana', () => {
    beforeAll(() => {
      mountComponent({ stubs: ['z-data-card-item', 'z-select', 'z-date-field', 'v-chip', 'z-keyword-filter-autocomplete'] })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should update phoneticFamilyName when it blur', () => {
      const input = wrapper.find('[data-phonetic-family-name-input]')
      input.setValue('たかぎ')
      input.trigger('blur')
      expect(wrapper.vm.$data.form.phoneticFamilyName).toBe('タカギ')
    })

    it('should update phoneticGivenName when it blur', () => {
      const input = wrapper.find('[data-phonetic-given-name-input]')
      input.setValue('れに')
      input.trigger('blur')
      expect(wrapper.vm.$data.form.phoneticGivenName).toBe('レニ')
    })
  })

  describe('formatPhoneNumber', () => {
    beforeAll(() => {
      mountComponent({ stubs: ['z-data-card-item', 'z-select', 'z-date-field', 'v-chip', 'z-keyword-filter-autocomplete'] })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should format tel', () => {
      const input = wrapper.find('[data-tel-input]')
      input.setValue('0359376825')
      input.trigger('blur')

      expect(wrapper.vm.$data.form.tel).toBe('03-5937-6825')
    })

    it('should format fax', () => {
      const input = wrapper.find('[data-fax-input]')
      input.setValue('0359376828')
      input.trigger('blur')

      expect(wrapper.vm.$data.form.fax).toBe('03-5937-6828')
    })
  })
})
