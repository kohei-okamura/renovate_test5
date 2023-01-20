/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { Wrapper } from '@vue/test-utils'
import { noop } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZIndexPageForm from '~/components/domain/staff/z-index-page-form.vue'
import { useAutofillWorkaround } from '~/composables/use-autofill-workaround'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-autofill-workaround')

describe('z-index-page-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const propsData = {
    errors: {},
    progress: false,
    hasUnauthorizedError: false
  }
  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    wrapper = mount(ZIndexPageForm, { propsData, mocks })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useAutofillWorkaround).mockReturnValue({
      autofilled: ref(false),
      autofilledField: ref(),
      unwatchAutofill: noop
    })
  })

  afterAll(() => {
    mocked(useAutofillWorkaround).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    const formValues = {
      email: 'john@example.com',
      password: 'PaSSWoRD'
    }
    let observer: ValidationObserverInstance
    const validate = async (values: Dictionary<string> = {}): Promise<void> => {
      await setData(wrapper, {
        form: { ...formValues, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      observer = getValidationObserver(wrapper)
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should fail when email is empty', async () => {
      await validate({ email: '' })
      expect(observer).not.toBePassed()
    })

    it('should fail when password is empty', async () => {
      await validate({ password: '' })
      expect(observer).not.toBePassed()
    })
  })
})
