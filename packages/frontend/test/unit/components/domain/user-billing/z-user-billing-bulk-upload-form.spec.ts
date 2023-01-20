/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZUserBillingBulkUploadForm from '~/components/domain/user-billing/z-user-billing-bulk-upload-form.vue'
import { WithdrawalTransactionsApi } from '~/services/api/withdrawal-transactions-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { STUB_DEFAULT_SEED } from '~~/stubs'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setProps } from '~~/test/helpers/set-props'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-user-billing-bulk-upload-form.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const ramen = ramenIpsum.factory(STUB_DEFAULT_SEED)
  const form: WithdrawalTransactionsApi.ImportForm = {
    file: new File([ramen.ipsum(4096)], 'dummy')
  }
  const propsData = {
    errors: {},
    progress: false,
    value: form
  }
  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ZUserBillingBulkUploadForm, { propsData, mocks })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should not display errors when props.value updated', async () => {
    mountComponent()
    const value = {
      file: undefined
    }
    await setProps(wrapper, { value })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<WithdrawalTransactionsApi.ImportForm> = {}): Promise<void> {
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

    it('should fail when file is empty', async () => {
      await validate({
        file: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-file] .v-messages').text()).toBe('入力してください。')
    })
  })
})
