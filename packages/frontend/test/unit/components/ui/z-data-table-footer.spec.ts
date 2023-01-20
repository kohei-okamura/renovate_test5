/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZDataTableFooter from '~/components/ui/z-data-table-footer.vue'
import { ItemsPerPageValuesStandard } from '~/models/items-per-page'
import { Pagination } from '~/models/pagination'
import { resizeWindow } from '~~/test/helpers/resize-window'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-data-table-footer.vue', () => {
  const { mount } = setupComponentTest()
  const pagination: Pagination = {
    count: 10000,
    desc: false,
    itemsPerPage: 10,
    page: 517,
    pages: 1000,
    sortBy: 'name'
  }

  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const propsData = {
      pagination,
      itemsPerPageOptionValues: ItemsPerPageValuesStandard
    }
    wrapper = mount(ZDataTableFooter, { propsData })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', async () => {
    await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.xs }, () => {
      expect(wrapper).toMatchSnapshot()
    })
    await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.xs - 1 }, () => {
      expect(wrapper).toMatchSnapshot()
    })
  })

  it('should be able to set itemsPerPage option values', () => {
    expect(wrapper.vm.$props.itemsPerPageOptionValues).toBe(ItemsPerPageValuesStandard)
  })

  it('should emit update:page event when click an item', async () => {
    await click(() => wrapper.find('.v-pagination__item'))

    const emitted = wrapper.emitted('update:page') ?? []
    expect(emitted).toHaveLength(1)
    expect(emitted[0]).toHaveLength(1)
    expect(emitted[0][0]).toBe(1)
  })

  it('should emit change event when change options', () => {
    const value = 100
    wrapper.find('[data-z-select-items-per-page]').vm.$emit('change', value)
    const emitted = wrapper.emitted('update:items-per-page') ?? []
    expect(emitted).toBeTruthy()
    expect(emitted[0]).toHaveLength(1)
    expect(emitted[0][0]).toBe(value)
  })
})
