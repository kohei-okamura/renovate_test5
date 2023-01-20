/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import deepmerge from 'deepmerge'
import Vue from 'vue'
import ZDwsBillingCopayCoordinationFormDialog
  from '~/components/domain/billing/z-dws-billing-copay-coordination-form-dialog.vue'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-dws-billing-copay-coordination-form-dialog.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const form: Partial<DwsBillingStatementsApi.UpdateCopayCoordinationForm> = {
    result: CopayCoordinationResult.coordinated,
    amount: 3200
  }
  const propsData = {
    dialog: true,
    amount: 9300,
    errors: {},
    progress: false,
    value: form
  }
  let wrapper: Wrapper<Vue & any>

  function mountComponent (data = propsData) {
    wrapper = mount(ZDwsBillingCopayCoordinationFormDialog, {
      propsData: data,
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  describe('initial display', () => {
    it('should be rendered correctly when registration', () => {
      const data = {
        ...propsData,
        value: { result: undefined, amount: undefined }
      }
      mountComponent(data)
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered correctly when update', () => {
      mountComponent()
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })
  })

  describe('validation', () => {
    type Values = Partial<Omit<typeof form, 'amount'> & { amount?: number | string }>
    let observer: ValidationObserverInstance

    async function validate (cb: () => void, values?: Values) {
      await setData(wrapper, {
        form: deepmerge(form, values ?? {})
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
      cb()
      observer.reset()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate(
        () => {
          expect(observer).toBePassed()
        }
      )
    })

    it('should fail if result is empty', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-result] .v-messages').text()).toBe('入力してください。')
        },
        {
          result: undefined
        }
      )
    })

    it('should fail if amount is empty', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-amount] .v-messages').text()).toBe('入力してください。')
        },
        {
          amount: undefined
        }
      )
    })

    it('should fail if amount is not number', async () => {
      await validate(
        () => {
          expect(observer).not.toBePassed()
          expect(wrapper.find('[data-amount] .v-messages').text()).toBe('半角数字のみで入力してください。')
        },
        {
          amount: 'abcde'
        }
      )
    })

    it.each([
      ['appropriated', CopayCoordinationResult.appropriated],
      ['notCoordinated', CopayCoordinationResult.notCoordinated],
      ['coordinated', CopayCoordinationResult.coordinated]
    ])('should not fail if amount is 0 when result is %s', async (_, result) => {
      await validate(
        () => {
          expect(observer).toBePassed()
        },
        {
          result,
          amount: 0
        }
      )
    })
  })

  describe('close', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should close dialog when cancel button clicked', async () => {
      await click(() => wrapper.find('[data-cancel]'))
      const events = wrapper.emitted('update:dialog') ?? []
      expect(events).toHaveLength(1)
      expect(events[0]).toStrictEqual([false])
    })
  })
})
