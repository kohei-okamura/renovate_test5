/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import { createMock } from '@zinger/helpers/testing/create-mock'
import ZPage from '~/components/ui/z-page.vue'
import { BreadcrumbsService, createBreadcrumbsService } from '~/services/breadcrumbs-service'
import { createTabService, TabService } from '~/services/tab-service'
import { resizeWindow } from '~~/test/helpers/resize-window'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-page.vue', () => {
  const { mount } = setupComponentTest()
  const MOBILE_BREAKPOINT = 960 // sm
  const $alert = {
    alertShow: false,
    config: reactive({
      color: 'info',
      title: '',
      text: ''
    })
  }
  const $breadcrumbs = createMock<BreadcrumbsService>({
    ...createBreadcrumbsService()
  })
  const $tabs = createMock<TabService>({
    ...createTabService()
  })
  const mocks = {
    $alert,
    $breadcrumbs,
    $tabs
  }

  describe('with breadcrumbs', () => {
    const propsData = {
      breadcrumbs: [
        { text: '一覧', to: '/path/to/index' },
        { text: '詳細' }
      ],
      title: 'DRAGON BALL Z'
    }

    it.each([
      ['desktop', MOBILE_BREAKPOINT],
      ['mobile', MOBILE_BREAKPOINT - 1]
    ])('should be rendered correctly on %s', async (_, width) => {
      await resizeWindow({ width }, () => {
        const wrapper = mount(ZPage, { mocks, propsData })
        expect(wrapper).toMatchSnapshot()
        wrapper.destroy()
      })
    })
  })

  describe('without breadcrumbs', () => {
    const propsData = {
      title: 'EUREKA SEVEN'
    }

    it.each([
      ['desktop', MOBILE_BREAKPOINT],
      ['mobile', MOBILE_BREAKPOINT - 1]
    ])('should be rendered correctly on %s', async (_, width) => {
      await resizeWindow({ width }, () => {
        const wrapper = mount(ZPage, { mocks, propsData })
        expect(wrapper).toMatchSnapshot()
        wrapper.destroy()
      })
    })
  })
})
