/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZProgress from '~/components/ui/z-progress.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-nuxt-child.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  function mountComponent (options?: MountOptions<Vue>) {
    const opts = { ...{ propsData: { value: true } }, ...options }
    wrapper = mount(ZProgress, opts)
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('change in appearance', () => {
    it('should display message that passed via props', async () => {
      const message = 'このメッセージを表示してください。'
      await wrapper.setProps({ message })
      expect(wrapper.find('[data-z-progress-message]').text()).toStrictEqual(message)
    })

    it('should set the color style when passed the css color via props', async () => {
      await wrapper.setProps({ color: '#888' })
      const expected = 'color: rgb(136, 136, 136)'
      expect(wrapper.findComponent({ ref: 'progress' }).attributes().style).toContain(expected)
      expect(wrapper.find('[data-z-progress-message]').attributes().style).toContain(expected)
    })

    it('should set the color class when passed the vuetify style color via props', async () => {
      await wrapper.setProps({ color: 'indigo darken-2' })
      const expected = 'indigo--text text--darken-2'
      expect(wrapper.findComponent({ ref: 'progress' }).attributes().class).toContain(expected)
      expect(wrapper.find('[data-z-progress-message]').attributes().class).toContain(expected)
    })
  })
})
