/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZOfficeGroupForm from '~/components/domain/office/z-office-group-form.vue'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createOfficeGroupResponseStub } from '~~/stubs/create-office-group-response-stub'
import { OFFICE_GROUP_ID_MIN } from '~~/stubs/create-office-group-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-office-group-form.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('officeGroups')
  const $form = createMockedFormService()
  const form: OfficeGroupsApi.Form = {
    name: '関東ブロック',
    parentOfficeGroupId: 30
  }
  const mocks = {
    $api,
    $form
  }
  const propsData = {
    buttonText: '登録',
    dialog: true,
    errors: {},
    progress: false,
    title: '事業所グループを登録',
    value: form
  }
  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ZOfficeGroupForm, {
      mocks,
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    jest.spyOn($api.officeGroups, 'get').mockResolvedValue(createOfficeGroupResponseStub(OFFICE_GROUP_ID_MIN))
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<OfficeGroupsApi.Form> = {}): Promise<void> {
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

    it('should fail when name is empty', async () => {
      await validate({
        name: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when name is longer than 100 characters', async () => {
      await validate({
        name: 'x'.repeat(100)
      })
      expect(observer).toBePassed()
      await validate({
        name: 'x'.repeat(101)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-name] .v-messages').text()).toBe('100文字以内で入力してください。')
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
