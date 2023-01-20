/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZSelectItemsPerPage from '~/components/ui/z-select-items-per-page.vue'
import { selectOptions } from '~/composables/select-options'
import { ItemsPerPage, ItemsPerPageLargeNumber, ItemsPerPageValuesLargeNumber } from '~/models/items-per-page'
import { VSelectOption } from '~/models/vuetify'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-select-items-per-page', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>
  const baseProps = {
    currentValue: 10
  }

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    wrapper = mount(ZSelectItemsPerPage, { propsData: baseProps })
    expect(wrapper).toMatchSnapshot()
  })

  it('should have default options', () => {
    wrapper = mount(ZSelectItemsPerPage, { propsData: baseProps })
    expect((wrapper.vm as any).items).toBeArray()
  })

  it('should be able to set options', () => {
    const options: VSelectOption<ItemsPerPage>[] = selectOptions<ItemsPerPageLargeNumber>(
      ItemsPerPageValuesLargeNumber.map(v => ({ text: `${v}`, value: v }))
    )
    const propsData = { ...baseProps, ...{ optionValues: ItemsPerPageValuesLargeNumber } }
    wrapper = mount(ZSelectItemsPerPage, { propsData })
    expect((wrapper.vm as any).items).toMatchObject(options)
  })

  it('should emit change event when change options', () => {
    const value = 20
    wrapper = mount(ZSelectItemsPerPage, { propsData: baseProps })
    wrapper.find('div[class*="v-select"]').vm.$emit('change', value)
    const emitted = wrapper.emitted('change') ?? []
    expect(emitted).toBeTruthy()
    expect(emitted[0]).toHaveLength(1)
    expect(emitted[0][0]).toBe(value)
  })
})
