/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZLtcsBillingForm from '~/components/domain/billing/z-ltcs-billing-form.vue'
import { useOffices } from '~/composables/use-offices'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('z-ltcs-billing-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const form: LtcsBillingsApi.CreateForm = {
    officeId: OFFICE_ID_MIN,
    transactedIn: '2020-10'
  }
  const propsData = {
    errors: {},
    progress: false,
    value: form,
    buttonText: '作成開始'
  }

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ZLtcsBillingForm, { propsData, mocks })
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

    async function validate (values: Partial<LtcsBillingsApi.CreateForm> = {}): Promise<void> {
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

    it.each([
      ['officeId', 'office', { officeId: undefined }],
      ['transactedIn', 'transactedIn', { transactedIn: undefined }]
    ])('should fail when %s is empty', async (_, name, value) => {
      const messageWrapper = wrapper.find(`[data-${name}] .v-messages`)
      expect(messageWrapper.text()).toBe('')
      await validate(value)
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('入力してください。')
    })
  })
})
