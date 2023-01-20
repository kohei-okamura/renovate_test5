/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { LtcsUserLocationAddition } from '@zinger/enums/lib/ltcs-user-location-addition'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import ZUserLtcsCalcSpecForm from '~/components/domain/user/z-user-ltcs-calc-spec-form.vue'
import { UserLtcsCalcSpecsApi } from '~/services/api/user-ltcs-calc-specs-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createUserLtcsCalcSpecStub } from '~~/stubs/create-user-ltcs-calc-spec-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-user-ltcs-calc-spec-form.vue', () => {
  type Form = DeepPartial<UserLtcsCalcSpecsApi.Form>

  const { mount, shallowMount } = setupComponentTest()
  const $form = createMockedFormService()
  const form: Form = {
    effectivatedOn: '2020-10-10',
    locationAddition: LtcsUserLocationAddition.mountainousArea
  }
  const calcSpec = createUserLtcsCalcSpecStub()
  const propsData = {
    buttonText: '登録',
    errors: {},
    permission: Permission.createUserLtcsCalcSpecs,
    progress: false,
    user: createUserStub(calcSpec.userId),
    value: { ...form }
  }
  const mocks = {
    $form
  }

  let wrapper: Wrapper<Vue & any>

  function mountComponent ({ options, isShallow }: { options?: MountOptions<Vue>, isShallow?: true } = {}) {
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(ZUserLtcsCalcSpecForm, {
      ...options,
      mocks,
      propsData
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

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<UserLtcsCalcSpecsApi.Form> = {}): Promise<void> {
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

    it('should fail when effectivatedOn is empty', async () => {
      await validate({
        effectivatedOn: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-effectivated-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when locationAddition is empty', async () => {
      await validate({
        locationAddition: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-location-addition] .v-messages').text()).toBe('入力してください。')
    })
  })
})
