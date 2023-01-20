/*
* Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
* UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
*/
import { Wrapper } from '@vue/test-utils'
import flushPromises from 'flush-promises'
import { Vue } from 'vue/types/vue'
import ZCancelConfirmDialog from '~/components/ui/z-cancel-confirm-dialog.vue'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-cancel-confirm-dialog.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue & any>

  beforeAll(() => {
    wrapper = mount(ZCancelConfirmDialog)
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('emit click:negative when z-prompt-dialog emits click:negative', () => {
    const zPromptDialogWrapper = wrapper.findComponent({ name: 'ZPromptDialog' })

    zPromptDialogWrapper.vm.$emit('click:negative', new Event('click'))
    const emitted = wrapper.emitted('click:negative')

    expect(emitted).toBeTruthy()
    expect(emitted).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual(false)
  })

  it('should not emit click:positive when z-prompt-dialog emits click:positive if a reason is not entered', async () => {
    const zPromptDialogWrapper = wrapper.findComponent({ name: 'ZPromptDialog' })
    zPromptDialogWrapper.vm.$emit('click:positive', new Event('click'))
    await flushPromises()

    const emitted = wrapper.emitted('click:positive')

    expect(emitted).toBeFalsy()
  })

  it('should emit click:positive when z-prompt-dialog emits click:positive if a reason is entered', async () => {
    const reason = 'キャンセル理由が入ります。'
    await setData(wrapper, { reason })
    const zPromptDialogWrapper = wrapper.findComponent({ name: 'ZPromptDialog' })
    zPromptDialogWrapper.vm.$emit('click:positive', new Event('click'))
    await flushPromises()

    const emitted = wrapper.emitted('click:positive')

    expect(emitted).toBeTruthy()
    expect(emitted).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual(reason)
  })
})
