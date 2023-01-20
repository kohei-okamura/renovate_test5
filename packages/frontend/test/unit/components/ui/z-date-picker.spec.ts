/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { nextTick } from '@nuxtjs/composition-api'
import { Wrapper } from '@vue/test-utils'
import { noop } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZDatePicker from '~/components/ui/z-date-picker.vue'
import { setProps } from '~~/test/helpers/set-props'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-date-picker.vue', () => {
  const { shallowMount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  function mountComponent (): void {
    wrapper = shallowMount(ZDatePicker)
  }

  function unmountComponent (): void {
    wrapper.destroy()
  }

  beforeEach(() => {
    mountComponent()
  })

  afterEach(() => {
    unmountComponent()
  })

  it.each([
    ['when no props specified', {}],
    ['when the prop "max" specified', { max: '2021-04-19' }],
    ['when the prop "min" specified', { min: '2008-05-17' }],
    ['when the prop "type" specified', { type: 'month' }],
    ['when the prop "pickerDate" specified', { pickerDate: '2021-03-01' }],
    ['when the prop "useJapaneseEra" specified', { useJapaneseEra: false }],
    ['when the prop "value" specified', { value: '2020-12-31' }]
  ])('should rendered correctly %s', async (_, props) => {
    await setProps(wrapper, props)
    expect(wrapper).toMatchSnapshot()
  })

  it.each([
    ['"multiple" is true, but "value" is string', { multiple: true, value: '' }],
    ['"multiple" is false, but "value" is array', { multiple: false, value: [] }]
  ])('should be thrown an error if invalid props are passed (%s)', async (_, props) => {
    let message = ''
    // Error in event handler for "hook:beforeUpdate" を回避する
    jest.spyOn(global.console, 'error').mockImplementation(noop)
    jest.spyOn(Vue.config, 'errorHandler').mockImplementationOnce(e => {
      message = e.message
    })
    jest.spyOn(Vue.config, 'errorHandler').mockImplementation(noop)

    await setProps(wrapper, props)
    await nextTick()
    expect(message).toMatch(/^If props.multiple is true/)

    mocked(Vue.config.errorHandler).mockRestore()
    mocked(global.console.error).mockRestore()
  })

  // TODO: なんとかしてテストしたい
  it.skip('should open in birthday mode when both of props: the birthday and the dialog are true', async () => {
    await setProps(wrapper, {
      birthday: true,
      dialog: true
    })
    expect(wrapper.findComponent({ ref: 'picker' }).vm.$data.activePicker).toBe('YEAR')
  })

  it.each([
    ['date', '2008-05-17', '2008-05-17'],
    ['month', '2021-03-30', '2021-03'],
    ['date', undefined, undefined]
  ])('should emit an event "input" when the v-date-picker emit an event "input"', async (type, date, expected) => {
    expect(wrapper.emitted('input')).toBeUndefined()
    await setProps(wrapper, { type })
    const picker = wrapper.findComponent({ ref: 'picker' })

    picker.vm.$emit('input', date)

    const emitted = wrapper.emitted('input')!
    expect(emitted).toHaveLength(1)
    expect(emitted[0]).toHaveLength(1)
    expect(emitted[0][0]).toBe(expected)
  })
})
