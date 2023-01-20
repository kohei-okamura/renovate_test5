/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import ZBreadcrumbs from '~/components/ui/z-breadcrumbs.vue'
import { VBreadcrumb } from '~/models/vuetify'
import { BreadcrumbsService, createBreadcrumbsService } from '~/services/breadcrumbs-service'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-breadcrumbs.vue', () => {
  const { mount } = setupComponentTest()
  const $breadcrumbs = createMock<BreadcrumbsService>({
    ...createBreadcrumbsService()
  })
  const defaultBreadcrumbs = [
    { text: 'パンくず1' },
    { text: 'パンくず2' },
    { text: 'パンくず3' }
  ]
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    wrapper = mount(ZBreadcrumbs, { mocks: { $breadcrumbs } })
  })

  beforeEach(() => {
    $breadcrumbs.update(defaultBreadcrumbs)
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should be updated when $breadcrumbs is updated', async () => {
    const newBreadcrumbs: VBreadcrumb[] = [
      { text: '新しいパンくず1', to: 'new/breadcrumbs1' },
      { text: '新しいパンくず2', to: 'new/breadcrumbs2' },
      { text: '新しいパンくず3', disabled: true }
    ]

    await $breadcrumbs.update(newBreadcrumbs)

    expect(wrapper).toMatchSnapshot()
  })
})
