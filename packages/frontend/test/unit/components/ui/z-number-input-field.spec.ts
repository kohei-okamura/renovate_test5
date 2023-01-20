/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import ZNumberInputField from '~/components/ui/z-number-input-field.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-number-input-field.vue', () => {
  const { mount } = setupComponentTest()

  it('should emit input event when input event occurred', async () => {
    const value = '120'
    const propsData = { value }
    const wrapper = mount(ZNumberInputField, { propsData })
    const textField = wrapper.find('[data-text-field]')
    const element = textField.element as HTMLInputElement
    element.value = value
    textField.trigger('input')

    await wrapper.vm.$nextTick()

    const emitted = wrapper.emitted('input') ?? []
    expect(emitted).toBeTruthy()
    expect(emitted[0]).toHaveLength(1)
    expect(emitted[0][0]).toStrictEqual(+value)

    wrapper.destroy()
  })
})
