/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Stubs, Wrapper } from '@vue/test-utils'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import { StaffStatus } from '@zinger/enums/lib/staff-status'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import Vue from 'vue'
import { HttpStatusCode } from '~/models/http-status-code'
import SignUpPage from '~/pages/invitations/_token.vue'
import { InvitationsApi } from '~/services/api/invitations-api'
import { StaffsApi } from '~/services/api/staffs-api'
import { $datetime } from '~/services/datetime-service'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createInvitationStub } from '~~/stubs/create-invitation-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

describe('pages/invitations/_token.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const $api = createMockedApi('invitations', 'staffs')
  const $route = createMockedRoute({
    params: {
      token: 'x'.repeat(60)
    }
  })
  const mocks = {
    $api,
    $route
  }
  const formValues: Omit<StaffsApi.CreateForm, 'invitationId' | 'token'> = {
    password: 'a'.repeat(8),
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
    certifications: [],
    status: StaffStatus.provisional
  }
  const invitation = createInvitationStub(1, 'x'.repeat(60))
  const stubs: Stubs = {
    'v-stepper': true,
    'v-stepper-content': true,
    'v-stepper-step': true
  }

  let wrapper: Wrapper<Vue>

  type MountComponentArguments = MountOptions<Vue> & {
    isShallow?: true
  }

  async function mountComponent ({ isShallow, ...options }: MountComponentArguments = {}) {
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(SignUpPage, {
      ...options,
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    // `mockResolvedValue` を使うと期待通り動かないので `mockImplementation` を用いる
    jest.spyOn($api.invitations, 'get').mockImplementation(() => Promise.resolve({ invitation }))
  })

  afterEach(() => {
    mocked($api.invitations.get).mockReset()
  })

  it('should be rendered correctly', async () => {
    await mountComponent({ stubs })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should not be rendered until invitation resolved', async () => {
    const deferred = new Deferred<InvitationsApi.GetResponse>()
    jest.spyOn($api.invitations, 'get').mockImplementation(() => deferred.promise)

    await mountComponent({ stubs })

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should display message when the invitation is expired', async () => {
    jest.spyOn($api.invitations, 'get').mockImplementation(() => {
      throw createAxiosError(HttpStatusCode.Forbidden)
    })

    await mountComponent({ stubs })

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<StaffsApi.CreateForm> = {}): Promise<void> {
      await setData(wrapper, {
        form: { ...formValues, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(async () => {
      await mountComponent({ stubs })
    })

    afterAll(() => {
      unmountComponent()
    })

    // FIXME: vee-validate を 3.0.11 から 3.1.1 にアップグレードしたら動かなくなった……
    it.skip('should pass when input correctly', async () => {
      observer = getValidationObserver(wrapper)
      await validate()
      expect(observer).toBePassed()
    })

    describe('step 1', () => {
      beforeAll(() => {
        observer = getValidationObserver(wrapper, 'observer1')
      })

      it('should pass when input correctly', async () => {
        await validate()
        expect(observer).toBePassed()
      })

      it('should fail when password is empty', async () => {
        await validate({
          password: ''
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-password] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail when password is shorter than 8', async () => {
        await validate({
          password: 'a'.repeat(7)
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-password] .v-messages').text()).toBe('8文字以上で入力してください。')
      })
    })

    describe('step 2', () => {
      beforeAll(() => {
        observer = getValidationObserver(wrapper, 'observer2')
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
    })

    describe('step 3', () => {
      beforeAll(() => {
        observer = getValidationObserver(wrapper, 'observer3')
      })

      it('should pass when input correctly', async () => {
        await validate()
        expect(observer).toBePassed()
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
    })
  })

  describe('autoKana', () => {
    beforeAll(async () => {
      await mountComponent({ stubs })
    })

    beforeEach(async () => {
      await setData(wrapper, { form: { ...formValues }, step: 2 })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should update phoneticFamilyName when it blur', async () => {
      await setData(wrapper, { form: { phoneticFamilyName: 'たかぎ' } })
      wrapper.find('[data-phonetic-family-name-input]').trigger('blur')
      expect(wrapper.vm.$data.form.phoneticFamilyName).toBe('タカギ')
    })

    it('should update phoneticGivenName when it blur', async () => {
      await setData(wrapper, { form: { phoneticGivenName: 'れに' } })
      wrapper.find('[data-phonetic-given-name-input]').trigger('blur')
      expect(wrapper.vm.$data.form.phoneticGivenName).toBe('レニ')
    })
  })

  describe('formatPhoneNumber', () => {
    beforeAll(async () => {
      await mountComponent({ stubs })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should format tel', async () => {
      await setData(wrapper, { form: formValues, step: 3 })
      const tel = wrapper.find('[data-tel-input]')

      tel.setValue('0359376825')
      tel.trigger('blur')

      expect(wrapper.vm.$data.form.tel).toBe('03-5937-6825')
    })

    it('should format fax', async () => {
      await setData(wrapper, { form: formValues, step: 3 })
      const fax = wrapper.find('[data-fax-input]')

      fax.setValue('0359376828')
      fax.trigger('blur')

      expect(wrapper.vm.$data.form.fax).toBe('03-5937-6828')
    })
  })

  describe('next', () => {
    const form = { ...formValues }

    beforeEach(async () => {
      await mountComponent()
      await setData(wrapper, { form })
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should display next step when validation succeed', async () => {
      const step1 = (): Wrapper<any> => wrapper.find('[data-step-1]')
      const step2 = (): Wrapper<any> => wrapper.find('[data-step-2]')
      const step3 = (): Wrapper<any> => wrapper.find('[data-step-3]')
      const step4 = (): Wrapper<any> => wrapper.find('[data-step-4]')

      expect(wrapper.vm.$data.step).toBe(1)
      expect(step1().vm.isActive).toBeTrue()
      expect(step2().vm.isActive).toBeFalse()
      expect(step3().vm.isActive).toBeFalse()
      expect(step4().vm.isActive).toBeFalse()

      await submit(() => wrapper.find('[data-form-1]'))

      expect(wrapper.vm.$data.step).toBe(2)
      expect(step1().vm.isActive).toBeFalse()
      expect(step2().vm.isActive).toBeTrue()
      expect(step3().vm.isActive).toBeFalse()
      expect(step4().vm.isActive).toBeFalse()

      await submit(() => wrapper.find('[data-form-2]'))

      expect(wrapper.vm.$data.step).toBe(3)
      expect(step1().vm.isActive).toBeFalse()
      expect(step2().vm.isActive).toBeFalse()
      expect(step3().vm.isActive).toBeTrue()
      expect(step4().vm.isActive).toBeFalse()

      await submit(() => wrapper.find('[data-form-3]'))

      expect(wrapper.vm.$data.step).toBe(4)
      expect(step1().vm.isActive).toBeFalse()
      expect(step2().vm.isActive).toBeFalse()
      expect(step3().vm.isActive).toBeFalse()
      expect(step4().vm.isActive).toBeTrue()

      await submit(() => wrapper.find('[data-form-4]'))

      expect(wrapper.vm.$data.step).toBe(5)
    })
  })

  describe('prev', () => {
    beforeEach(async () => {
      await mountComponent()
      await setData(wrapper, {
        form: { ...formValues },
        step: 5
      })
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should display previous step', async () => {
      const step1 = (): Wrapper<any> => wrapper.find('[data-step-1]')
      const step2 = (): Wrapper<any> => wrapper.find('[data-step-2]')
      const step3 = (): Wrapper<any> => wrapper.find('[data-step-3]')
      const step4 = (): Wrapper<any> => wrapper.find('[data-step-4]')

      expect(wrapper.vm.$data.step).toBe(5)

      await click(() => wrapper.find('[data-prev-5]'))

      expect(wrapper.vm.$data.step).toBe(4)
      expect(step1().vm.isActive).toBeFalse()
      expect(step2().vm.isActive).toBeFalse()
      expect(step3().vm.isActive).toBeFalse()
      expect(step4().vm.isActive).toBeTrue()

      await click(() => wrapper.find('[data-prev-4]'))

      expect(wrapper.vm.$data.step).toBe(3)
      expect(step1().vm.isActive).toBeFalse()
      expect(step2().vm.isActive).toBeFalse()
      expect(step3().vm.isActive).toBeTrue()
      expect(step4().vm.isActive).toBeFalse()

      await click(() => wrapper.find('[data-prev-3]'))

      expect(wrapper.vm.$data.step).toBe(2)
      expect(step1().vm.isActive).toBeFalse()
      expect(step2().vm.isActive).toBeTrue()
      expect(step3().vm.isActive).toBeFalse()
      expect(step4().vm.isActive).toBeFalse()

      await click(() => wrapper.find('[data-prev-2]'))

      expect(wrapper.vm.$data.step).toBe(1)
    })
  })

  describe('submit', () => {
    const form = { ...formValues }

    beforeEach(async () => {
      await mountComponent({ isShallow: true, stubs })
      jest.spyOn($api.staffs, 'create').mockResolvedValue(undefined)
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should call $api.staffs.create', async () => {
      await setData(wrapper, { form, step: 5 })

      await click(() => wrapper.find('[data-submit]'))

      expect($api.staffs.create).toHaveBeenCalledTimes(1)
      expect($api.staffs.create).toHaveBeenCalledWith({
        form: {
          ...form,
          invitationId: invitation.id,
          token: invitation.token
        }
      })
    })

    it('should display errors when api responses bad request', async () => {
      const message = 'このメールアドレスはすでに使用されているため、登録できません。'
      const error = createAxiosError(HttpStatusCode.BadRequest, {
        errors: {
          invitationId: [message]
        }
      })
      jest.spyOn($api.staffs, 'create').mockRejectedValue(error)

      await setData(wrapper, { form, step: 5 })
      await click(() => wrapper.find('[data-submit]'))

      const targetWrapper = wrapper.find('[data-alert]')

      expect(targetWrapper.text()).toContain(message)
      expect(targetWrapper).toMatchSnapshot()
    })

    // TODO: 異常系のテストを追加する
  })
})
