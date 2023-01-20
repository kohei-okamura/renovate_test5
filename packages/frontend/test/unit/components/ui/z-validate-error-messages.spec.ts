/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZValidateErrorMessages from '~/components/ui/z-validate-error-messages.vue'
import { required } from '~/support/validation/rules'
import { ValidationObserverInstance } from '~/support/validation/types'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-validate-error-messages.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>
  let observer: ValidationObserverInstance
  const defaultProps = {
    rules: { required },
    value: 'test input'
  }

  async function mountConponent (propsData: { rules: Record<string, unknown>, value: string } = defaultProps) {
    wrapper = mount(ZValidateErrorMessages, { propsData })
    observer = wrapper.findComponent({ ref: 'provider' }).vm as ValidationObserverInstance
    await observer.validate()
  }

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    mountConponent()
    expect(wrapper).toMatchSnapshot()
  })

  it('should fail when value is empty', async () => {
    await mountConponent({ rules: { required }, value: '' })
    expect(wrapper.find('.v-messages').text()).toBe('入力してください。')
    expect(wrapper).toMatchSnapshot()
  })
})
