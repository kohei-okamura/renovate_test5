/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import LtcsBillingStatementsForm from '~/components/domain/billing/z-ltcs-billings-statements-form.vue'
import { LtcsBillingStatement } from '~/models/ltcs-billing-statement'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createLtcsBillingStatementResponseStub } from '~~/stubs/create-ltcs-billing-statement-response-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-ltcs-billings-statements-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }

  const { statement } = createLtcsBillingStatementResponseStub()
  const aggregates = statement.aggregates
  const formValue = reactive(Object.fromEntries(aggregates.map(x => {
    return [x.serviceDivisionCode, String(x.plannedScore)]
  })))
  const form: LtcsBillingStatement = {
    ...statement
  }
  const propsData = {
    errors: {},
    progress: false,
    value: formValue,
    statement: form,
    canUpdateContent: true
  }
  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(LtcsBillingStatementsForm, { propsData, mocks })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: { [p: string]: string } = {}): Promise<void> {
      await setData(wrapper, {
        form: { ...formValue, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeEach(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should fail when plannedScore is over max number', async () => {
      const messageWrapper = wrapper.find('[data-planned-score] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ 11: '797906' })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('限度額管理対象単位数以下の値を入力してください。')
    })

    it('should fail when plannedScore is negative number', async () => {
      const messageWrapper = wrapper.find('[data-planned-score] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ 11: '-1' })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('半角数字のみで入力してください。')
    })

    it('should not fail when plannedScore is zero', async () => {
      const messageWrapper = wrapper.find('[data-planned-score] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ 11: '0' })
      expect(observer).toBePassed()
      expect(messageWrapper.text()).toBe('')
    })
  })
})
