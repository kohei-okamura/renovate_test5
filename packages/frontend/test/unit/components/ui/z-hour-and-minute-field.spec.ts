/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZHourAndMinuteField from '~/components/ui/z-hour-and-minute-field.vue'
import { TimeDuration } from '~/models/time-duration'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { blur } from '~~/test/helpers/trigger'

describe('z-hour-and-minute-field.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    wrapper = mount(ZHourAndMinuteField)
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it.each([
    'hour',
    'minute'
  ])('should emit input event when input event occurred in %s-text-field', async (key: string) => {
    const value = '3'
    const time = '10:30'
    const duration = TimeDuration.from(time).get
    const verifier = (key === 'hour' ? duration.withHours : duration.withMinutes).bind(duration)
    const propsData = { value: time }
    const localWrapper = mount(ZHourAndMinuteField, { propsData })
    const textField = localWrapper.find(`[data-${key}-text-field]`)
    const element = textField.element as HTMLInputElement
    element.value = value
    textField.trigger('input')

    await localWrapper.vm.$nextTick()

    const emitted = localWrapper.emitted('input') ?? []
    expect(emitted).toBeTruthy()
    expect(emitted[0]).toHaveLength(1)
    expect(emitted[0][0]).toStrictEqual(verifier(+value))
  })

  it.each([
    'hour',
    'minute'
  ])('should emit blur event when blur event occurred in %s-text-field', async (key: string) => {
    await blur(() => wrapper.find(`[data-${key}-text-field]`))
    const emitted = wrapper.emitted('blur')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toHaveLength(1)
    expect(emitted![0][0]).toBeInstanceOf(FocusEvent)
  })
})
