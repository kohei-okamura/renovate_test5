/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import ZTabs from '~/components/ui/z-tabs.vue'
import { VTab } from '~/models/vuetify'
import { createTabService, TabService } from '~/services/tab-service'
import { resizeWindow } from '~~/test/helpers/resize-window'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-tabs.vue', () => {
  const { mount } = setupComponentTest()
  const $tabs = createMock<TabService>({
    ...createTabService()
  })
  const defaultTabs = [
    { label: 'タブ1', to: '/tab1' },
    { label: 'タブ2', to: '/tab2' },
    { label: 'タブ3', to: '/tab3' }
  ]
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    wrapper = mount(ZTabs, { mocks: { $tabs } })
  })

  beforeEach(() => {
    $tabs.update(defaultTabs)
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it.each([
    ['less than', 1],
    ['greater than or equal', 0]
  ])('should be rendered correctly when %s a breakpoint', async (_, subtract) => {
    const { thresholds: { md }, scrollBarWidth } = wrapper.vm.$vuetify.breakpoint
    await resizeWindow({ width: md - scrollBarWidth - subtract }, () => {
      wrapper.vm.$nextTick()
      expect(wrapper).toMatchSnapshot()
    })
  })

  it('should be updated when $tabs is updated', async () => {
    const newTabs: VTab[] = [
      { label: '新しいタブ1', to: '/new/tab1' },
      { label: '新しいタブ2', to: '/new/tab2' },
      { label: '新しいタブ3', to: '/new/tab3' }
    ]

    await $tabs.update(newTabs)

    expect(wrapper).toMatchSnapshot()
  })
})
