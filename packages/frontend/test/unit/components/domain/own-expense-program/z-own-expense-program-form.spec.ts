/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { TaxCategory } from '@zinger/enums/lib/tax-category'
import { TaxType } from '@zinger/enums/lib/tax-type'
import { assign } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZOwnExpenseProgramForm from '~/components/domain/own-expense-program/z-own-expense-program-form.vue'
import { useOffices } from '~/composables/use-offices'
import { Expense } from '~/models/expense'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-offices')

describe('z-own-expense-program-form.vue', () => {
  type Form =
    Omit<OwnExpenseProgramsApi.Form, 'durationMinutes' | 'fee'>
    & { fee: Pick<Expense, 'taxType' | 'taxCategory'> }

  type NumericForm = {
    durationMinutes: string | number
    taxExcluded: string | number
    taxIncluded: string | number
    taxExemptedFee: string | number
  }

  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const form: Form = {
    officeId: OFFICE_ID_MIN,
    name: '魚粉マシマシで。',
    fee: {
      taxType: TaxType.taxExcluded,
      taxCategory: TaxCategory.consumptionTax
    },
    note: 'サンマーメンも好きだ。'
  }
  const numericForm = {
    durationMinutes: 100,
    taxExcluded: 100,
    taxIncluded: 110
  }
  const propsData = {
    buttonText: '登録',
    errors: {},
    permission: Permission.createOwnExpensePrograms,
    progress: false,
    value: { ...form }
  }
  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZOwnExpenseProgramForm, {
      mocks,
      ...options,
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    function validate (values?: DeepPartial<OwnExpenseProgramsApi.Form>) {
      return async function (obj?: DeepPartial<NumericForm>) {
        await setData(wrapper, {
          form: { ...form, ...values },
          ...numericForm,
          ...obj
        })
        await observer.validate()
        jest.runOnlyPendingTimers()
      }
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()()
      expect(observer).toBePassed()
    })

    it('should not fail when officeId is empty', async () => {
      await validate({
        officeId: undefined
      })()
      expect(observer).toBePassed()
    })

    it('should fail when name is empty', async () => {
      await validate({
        name: ''
      })()
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when name is longer than 200', async () => {
      await validate({
        name: '三'.repeat(200)
      })()
      expect(observer).toBePassed()

      await validate({
        name: '三'.repeat(201)
      })()
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-name] .v-messages').text()).toBe('200文字以内で入力してください。')
    })

    it('should fail when durationMinutes is empty', async () => {
      await validate()({ durationMinutes: '' })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-duration-minutes] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric durationMinutes given', async () => {
      await validate()({ durationMinutes: 'abc' })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-duration-minutes] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })

    it('should fail when durationMinutes is over than ONE_DAY_MINUTE = 1440 number', async () => {
      await validate()({ durationMinutes: 1440 })
      expect(observer).toBePassed()

      await validate()({ durationMinutes: 1441 })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-duration-minutes] .v-messages').text()).toBe('1以上、1440以下の半角数字で入力してください。')
    })

    it('should fail when taxExcluded is empty and taxType is taxExcluded', async () => {
      await validate()({ taxExcluded: '', taxIncluded: '' })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tax-excluded] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric taxExcluded given', async () => {
      await validate(
      )({ taxExcluded: 'abc', taxIncluded: 'abc' })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tax-excluded] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })

    it('should fail when taxIncluded is empty and taxType is taxIncluded', async () => {
      await validate({
        fee: {
          taxType: TaxType.taxIncluded
        }
      })({ taxIncluded: '' })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tax-included] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric taxIncluded given', async () => {
      await validate({
        fee: {
          taxType: TaxType.taxIncluded
        }
      })({ taxIncluded: 'abc' })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tax-included] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })

    it('should fail when taxExemptedFee empty and taxType is taxExempted', async () => {
      await validate({
        fee: {
          taxType: TaxType.taxExempted
        }
      })({ taxExcluded: '' })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tax-exempted-fee] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when non-numeric taxExemptedFee given', async () => {
      await validate({
        fee: {
          taxType: TaxType.taxExempted
        }
      })({ taxExemptedFee: 'abc' })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tax-exempted-fee] .v-messages').text()).toBe('半角数字のみで入力してください。')
    })

    it('should fail when taxType is empty', async () => {
      await validate({
        fee: {
          taxType: undefined
        }
      })()
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tax-type] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when taxCategory is empty and taxType is taxExcluded', async () => {
      await validate({
        fee: {
          taxCategory: undefined,
          taxType: TaxType.taxExcluded
        }
      })()
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-tax-category] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when note is longer than 255', async () => {
      await validate({
        note: '三'.repeat(255)
      })({
        durationMinutes: 24,
        taxExcluded: 1000,
        taxIncluded: 1100
      })
      expect(observer).toBePassed()

      await validate({
        note: '三'.repeat(256)
      })({
        durationMinutes: 24,
        taxExcluded: 1000,
        taxIncluded: 1100
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-note] .v-messages').text()).toBe('255文字以内で入力してください。')
    })
  })

  describe('input form', () => {
    beforeEach(() => {
      mountComponent()
    })

    afterEach(() => {
      unmountComponent()
    })

    describe('taxType(taxExcluded)', () => {
      const values = {
        form: {
          fee: {
            taxType: TaxType.taxExcluded,
            taxCategory: TaxCategory.consumptionTax
          }
        },
        taxExcluded: 100,
        taxIncluded: 110
      }

      it('should set taxExcluded, taxIncluded and taxIncluded of empty when change other than taxExcluded', async () => {
        await setData(wrapper, values)
        const fee = wrapper.vm.$data.form.fee
        await assign(fee, { taxType: TaxType.taxIncluded })
        expect(wrapper.vm.$data.taxExcluded).toBe('')
        expect(wrapper.vm.$data.taxIncluded).toBe('')
        expect(fee.taxCategory).toBeUndefined()
      })

      it('should set taxIncluded value automatically when taxExcluded is inputted', async () => {
        await setData(wrapper, values)
        wrapper.find('[data-tax-excluded-input]').setValue(1234)
        wrapper.find('[data-tax-excluded-input]').trigger('input')
        expect(wrapper.vm.$data.taxExcluded).toBe(1234)
        expect(wrapper.vm.$data.taxIncluded).toBe(Math.floor(1234 * Number(process.env.consumptionTax)))
      })
    })

    describe('taxType(taxIncluded)', () => {
      const values = {
        form: {
          fee: {
            taxType: TaxType.taxIncluded,
            taxCategory: TaxCategory.consumptionTax
          }
        },
        taxExcluded: 100,
        taxIncluded: 110
      }
      it('should set taxExcluded, taxIncluded and taxIncluded of empty when change other than taxIncluded', async () => {
        await setData(wrapper, values)
        const fee = wrapper.vm.$data.form.fee
        await assign(fee, { taxType: TaxType.taxExcluded })
        expect(wrapper.vm.$data.taxExcluded).toBe('')
        expect(wrapper.vm.$data.taxIncluded).toBe('')
        expect(fee.taxCategory).toBeUndefined()
      })

      it('should set taxExcluded value automatically when taxIncluded is inputted', async () => {
        await setData(wrapper, values)
        const fee = wrapper.vm.$data.form.fee
        await assign(fee, { taxCategory: TaxCategory.consumptionTax })
        wrapper.find('[data-tax-included-input]').setValue(1234)
        wrapper.find('[data-tax-included-input]').trigger('input')
        expect(wrapper.vm.$data.taxIncluded).toBe(1234)
        expect(wrapper.vm.$data.taxExcluded).toBe(Math.ceil(1234 / Number(process.env.consumptionTax)))
      })
    })

    describe('taxType(taxExempted)', () => {
      const values = {
        form: {
          fee: {
            taxType: TaxType.taxIncluded,
            taxCategory: TaxCategory.consumptionTax
          }
        },
        taxExcluded: 100,
        taxIncluded: 110
      }
      it('should set taxExcluded, taxIncluded and taxCategory value automatically when taxType change taxExempted', async () => {
        await setData(wrapper, values)
        const fee = wrapper.vm.$data.form.fee
        await assign(fee, { taxType: TaxType.taxExempted })
        expect(wrapper.vm.$data.taxExcluded).toBe('')
        expect(wrapper.vm.$data.taxIncluded).toBe('')
        expect(fee.taxCategory).toBe(TaxCategory.unapplicable)
      })

      it('should set taxIncluded and taxExcluded value automatically when taxExemptedFee is inputted', async () => {
        await setData(wrapper, values)
        const fee = wrapper.vm.$data.form.fee
        await assign(fee, { taxType: TaxType.taxExempted })
        wrapper.find('[data-tax-exempted-fee-input]').setValue(1234)
        wrapper.find('[data-tax-exempted-fee-input]').trigger('input')
        expect(wrapper.vm.$data.taxIncluded).toBe(1234)
        expect(wrapper.vm.$data.taxExcluded).toBe(1234)
      })
    })

    describe('taxCategory', () => {
      const values = {
        form: {
          fee: {
            taxType: TaxType.taxExcluded,
            taxCategory: TaxCategory.consumptionTax
          }
        },
        taxExcluded: 1234
      }
      it('make tax rate into process.env.consumptionTax when is consumptionTax', async () => {
        await setData(wrapper, values)
        expect(wrapper.vm.$data.taxIncluded).toBe(Math.floor(1234 * Number(process.env.consumptionTax)))
      })

      it('make tax rate into process.env.consumptionTax when is reducedConsumptionTax', async () => {
        await setData(wrapper, values)
        const fee = wrapper.vm.$data.form.fee
        await assign(fee, { taxCategory: TaxCategory.reducedConsumptionTax })
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.$data.taxIncluded).toBe(Math.floor(1234 * Number(process.env.reducedConsumptionTax)))
      })

      it('make tax rate into 1 when is unapplicable', async () => {
        await setData(wrapper, values)
        const fee = wrapper.vm.$data.form.fee
        await assign(fee, { taxCategory: TaxCategory.unapplicable })
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.$data.taxIncluded).toBe(1234)
      })
    })
  })

  describe('submit', () => {
    it('should be replaced by undefined if officeId is 0 when submitting the form', async () => {
      await mountComponent()
      await setData(wrapper, {
        form: { ...form, officeId: 0 },
        ...numericForm
      })
      await submit(() => wrapper.find('[data-form]'))
      expect(wrapper.emitted('submit')![0][0]).toEqual(expect.objectContaining({ officeId: undefined }))
      unmountComponent()
    })
  })
})
