/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import { MountOptions, Stubs, Wrapper } from '@vue/test-utils'
import { isEmpty } from '@zinger/helpers'
import Vue from 'vue'
import DwsBillingStatementsForm from '~/components/domain/billing/z-dws-billings-statements-form.vue'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { mapValues } from '~/support/utils/map-values'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createDwsBillingBundleStub } from '~~/stubs/create-dws-billing-bundle-stub'
import { createDwsBillingStatementResponseStub } from '~~/stubs/create-dws-billing-statement-response-stub'
import { createDwsBillingStatementStub } from '~~/stubs/create-dws-billing-statement-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-dws-billings-statements-form.vue', () => {
  const { mount } = setupComponentTest()
  const $route = createMockedRoute({ hash: '' })
  const $form = createMockedFormService()

  const bundle = createDwsBillingBundleStub()
  const stub = createDwsBillingStatementStub({ bundle })
  const responseStub = createDwsBillingStatementResponseStub({ id: stub.id })
  const aggregates = responseStub.statement.aggregates
  const formValue = reactive(Object.fromEntries(aggregates.map(x => {
    return [
      x.serviceDivisionCode,
      {
        managedCopay: String(x.managedCopay),
        subtotalSubsidy: isEmpty(x.subtotalSubsidy) ? undefined : String(x.subtotalSubsidy)
      }
    ]
  })))
  const form: DwsBillingStatement = {
    ...responseStub.statement
  }
  const propsData = {
    errors: {},
    progress: false,
    value: formValue,
    statement: form,
    canUpdateContent: true
  }
  let wrapper: Wrapper<Vue>

  type MountParameters = {
    options?: MountOptions<Vue>
  }

  function mountComponent ({ options }: MountParameters = {}) {
    const mocks = {
      $route,
      $form,
      ...options?.mocks
    }
    const stubs: Stubs = {
      ...options?.stubs
    }
    wrapper = mount(DwsBillingStatementsForm, {
      propsData,
      ...options,
      mocks,
      stubs
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should not be rendered subtotalSubsidy form card when subtotalSubsidy is empty', () => {
    mountComponent({
      options: {
        propsData: {
          ...propsData,
          statement: {
            ...form,
            aggregates: [...form.aggregates.map(x => ({ ...x, subtotalSubsidy: undefined }))]
          },
          value: mapValues(formValue, value => ({ ...value, subtotalSubsidy: undefined }))
        }
      }
    })
    expect(wrapper.find('[data-subtotal-subsidy="0"]')).not.toExist()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    it('should pass when input correctly', async () => {
      mountComponent()
      observer = getValidationObserver(wrapper)
      await observer.validate()
      jest.runOnlyPendingTimers()
      expect(observer).toBePassed()
      unmountComponent()
    })

    it('should fail when managedCopay is negative number', async () => {
      mountComponent()
      await setData(wrapper, {
        form: { 12: { managedCopay: '-1', subtotalSubsidy: '111' } }
      })
      observer = getValidationObserver(wrapper)
      await observer.validate()
      jest.runOnlyPendingTimers()

      const messageWrapper = wrapper.find('[data-managed-copay="0"] .v-messages')

      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('半角数字のみで入力してください。')

      unmountComponent()
    })

    it('should not fail when managedCopay is zero', async () => {
      mountComponent({
        options: { stubs: { 'z-form-card-item': false } }
      })
      await setData(wrapper, {
        form: { 12: { managedCopay: '0', subtotalSubsidy: '111' } }
      })
      observer = getValidationObserver(wrapper)
      await observer.validate()
      jest.runOnlyPendingTimers()
      expect(observer).toBePassed()

      unmountComponent()
    })

    it('should fail when subtotalSubsidy is negative number', async () => {
      mountComponent()
      await setData(wrapper, {
        form: { 12: { managedCopay: '111', subtotalSubsidy: '-1' } }
      })
      observer = getValidationObserver(wrapper)
      await observer.validate()
      jest.runOnlyPendingTimers()

      const messageWrapper = wrapper.find('[data-subtotal-subsidy="0"] .v-messages')

      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('半角数字のみで入力してください。')

      unmountComponent()
    })

    it('should not fail when subtotalSubsidy is zero', async () => {
      mountComponent({
        options: { stubs: { 'z-form-card-item': false } }
      })
      await setData(wrapper, {
        form: { 12: { managedCopay: '111', subtotalSubsidy: '0' } }
      })
      observer = getValidationObserver(wrapper)
      await observer.validate()
      jest.runOnlyPendingTimers()
      expect(observer).toBePassed()

      unmountComponent()
    })
  })
})
