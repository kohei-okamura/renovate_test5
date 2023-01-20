/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { nextTick } from '@nuxtjs/composition-api'
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZDateField from '~/components/ui/z-date-field.vue'
import { $icons } from '~/plugins/icons'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-date-field.vue', () => {
  const { shallowMount } = setupComponentTest()

  let wrapper: Wrapper<Vue>

  function mountComponent (options: Partial<MountOptions<Vue>> = {}): void {
    wrapper = shallowMount(ZDateField, {
      ...options,
      stubs: { 'v-btn': true }
    })
  }

  function unmountComponent (): void {
    wrapper.destroy()
  }

  it.each([
    ['when no props are specified', {}],
    ['when the "birthday" prop is specified', { birthday: true }],
    ['when the "clearable" prop is specified', { clearable: false }],
    ['when the "errorMessages" prop is specified', { errorMessages: ['入力してください。', 'エラーですよ。'] }],
    ['when the "label" prop is specified', { label: '生年月日' }],
    ['when the "type" prop is specified', { type: 'month' }],
    ['when the "useJapaneseEra" prop is specified', { useJapaneseEra: false }],
    ['when the "value" prop is specified', { value: '2008-05-17' }],
    ['when the "useJapaneseEra" and "value" props are specified', { useJapaneseEra: false, value: '2008-05-17' }]
  ])('should be rendered correctly %s', (_, propsData) => {
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it.each([
    ['when the "hide-details" attribute is specified', { 'hide-details': 'true' }],
    ['when the "max" attribute is specified', { max: '2021-04-20' }],
    ['when the "min" attribute is specified', { min: '1995-06-04' }],
    ['when the "picker-date" attribute is specified', { 'picker-date': '2020-05-05' }],
    ['when the "prepend-icon" attribute is specified', { 'prepend-icon': $icons.date }],
    ['when a data-xxx attribute is specified', { 'data-some-awesome-attribute': '' }]
  ])('should be rendered correctly %s', (_, attrs) => {
    mountComponent({ attrs })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should open the date-picker when the text-field clicked', async () => {
    mountComponent()
    wrapper.findComponent({ name: 'z-text-field' }).vm.$emit('click', new MouseEvent('click'))
    jest.runOnlyPendingTimers()
    await nextTick()

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should open the date-picker when the text-field focused', async () => {
    mountComponent()
    wrapper.findComponent({ name: 'z-text-field' }).vm.$emit('focus', new FocusEvent('focus'))
    jest.runOnlyPendingTimers()
    await nextTick()

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it.each([
    ['when click outside of the dialog', '[data-z-date-field-dialog]', 'click:outside', new MouseEvent('click')],
    ['when input the date-picker', '[data-z-date-field-picker]', 'input', '2021-04-19'],
    ['when click the "clear" button', '[data-z-date-field-button-clear]', 'click', new MouseEvent('click')],
    ['when click the "cancel" button', '[data-z-date-field-button-cancel]', 'click', new MouseEvent('click')]
  ])('should close the date-picker %s', async (_, selector, event, payload) => {
    mountComponent()
    wrapper.findComponent({ name: 'z-text-field' }).vm.$emit('focus', new FocusEvent('focus'))
    jest.runOnlyPendingTimers()
    await nextTick()

    wrapper.find(selector).vm.$emit(event, payload)
    await nextTick()

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it.each([
    ['when click outside of the dialog', '[data-z-date-field-dialog]', 'click:outside', new MouseEvent('click')],
    ['when input the date-picker', '[data-z-date-field-picker]', 'input', '2021-04-19'],
    ['when click the "clear" button', '[data-z-date-field-button-clear]', 'click', new MouseEvent('click')],
    ['when click the "cancel" button', '[data-z-date-field-button-cancel]', 'click', new MouseEvent('click')]
  ])('should emit a "blur" event %s', (_, selector, event, payload) => {
    mountComponent()
    expect(wrapper.emitted('blur')).toBeUndefined()

    wrapper.find(selector).vm.$emit(event, payload)

    const emitted = wrapper.emitted('blur')!
    expect(emitted).toHaveLength(1)
    unmountComponent()
  })

  it.each([
    ['when input the date-picker', '[data-z-date-field-picker]', 'input', '2021-04-19', '2021-04-19'],
    ['when click the "clear" button', '[data-z-date-field-button-clear]', 'click', new MouseEvent('click'), undefined]
  ])('should emit an "input" event %s', (_, selector, event, payload, expected) => {
    mountComponent()
    expect(wrapper.emitted('input')).toBeUndefined()

    wrapper.find(selector).vm.$emit(event, payload)

    const emitted = wrapper.emitted('input')!
    expect(emitted).toHaveLength(1)
    expect(emitted[0]).toHaveLength(1)
    expect(emitted[0][0]).toBe(expected)
    unmountComponent()
  })

  it.each([
    ['when click outside of the dialog', '[data-z-date-field-dialog]', 'click:outside', new MouseEvent('click')],
    ['when click the "cancel" button', '[data-z-date-field-button-cancel]', 'click', new MouseEvent('click')]
  ])('should not emit an "input" event %s', (_, selector, event, payload) => {
    mountComponent()
    wrapper.find(selector).vm.$emit(event, payload)
    expect(wrapper.emitted('input')!).toBeUndefined()
    unmountComponent()
  })
})
