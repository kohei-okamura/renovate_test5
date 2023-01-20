/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Vue } from 'vue/types/vue'
import ZSelectSearchCondition from '~/components/ui/z-select-search-condition.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-select-search-condition.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue & any>

  beforeAll(() => {
    const propsData = {
      items: []
    }
    wrapper = mount(ZSelectSearchCondition, { propsData })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should emit input event when z-select is cleared', async () => {
    const zSelect = wrapper.findComponent({ name: 'ZSelect' })

    zSelect.vm.$emit('click:clear')
    await wrapper.vm.$nextTick()

    const emitted = wrapper.emitted('input')
    expect(emitted).toBeTruthy()
    expect(emitted).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual('')
  })
})
