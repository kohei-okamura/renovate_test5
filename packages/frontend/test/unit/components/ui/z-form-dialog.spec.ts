/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZFormDialog from '~/components/ui/z-form-dialog.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

describe('z-form-dialog.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const propsData = {
      title: 'テスト',
      value: true
    }
    wrapper = mount(ZFormDialog, { propsData })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should close when the falsy value given', () => {
    const propsData = {
      value: true
    }
    const wrapper = mount(ZFormDialog, { propsData })
    expect(wrapper).toMatchSnapshot()
    wrapper.setProps({ value: false })
    expect(wrapper).toMatchSnapshot()
  })

  it('should emit submit event when submit forms', async () => {
    await submit(() => wrapper.find('[data-form]'))
    const emitted = wrapper.emitted('submit')
    expect(emitted).toBeTruthy()
    expect(emitted).toHaveLength(1)
  })
})
