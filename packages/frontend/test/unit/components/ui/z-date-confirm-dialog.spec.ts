/*
* Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
* UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
*/
import { Wrapper } from '@vue/test-utils'
import flushPromises from 'flush-promises'
import { Vue } from 'vue/types/vue'
import ZDateConfirmDialog from '~/components/ui/z-date-confirm-dialog.vue'
import { ISO_DATE_FORMAT, ISO_DATETIME_FORMAT } from '~/models/date'
import { TEST_NOW } from '~~/test/helpers/date'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-date-confirm-dialog.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue & any>

  beforeAll(() => {
    wrapper = mount(ZDateConfirmDialog, {
      slots: {
        option: 'オプション'
      }
    })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should emit click:negative when z-prompt-dialog emits click:negative', () => {
    const zPromptDialogWrapper = wrapper.findComponent({ name: 'ZPromptDialog' })

    zPromptDialogWrapper.vm.$emit('click:negative', new Event('click'))
    const emitted = wrapper.emitted('click:negative')

    expect(emitted).toBeTruthy()
    expect(emitted).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual(false)
  })

  it('should not emit click:positive when z-prompt-dialog emits click:positive if a date is not selected', async () => {
    const zPromptDialogWrapper = wrapper.findComponent({ name: 'ZPromptDialog' })
    zPromptDialogWrapper.vm.$emit('click:positive', new Event('click'))
    await flushPromises()

    const emitted = wrapper.emitted('click:positive')

    expect(emitted).toBeFalsy()
  })

  it('should display errors when z-prompt-dialog emits click:positive if a date is not selected', async () => {
    const zPromptDialogWrapper = wrapper.findComponent({ name: 'ZPromptDialog' })
    zPromptDialogWrapper.vm.$emit('click:positive', new Event('click'))
    await flushPromises()

    await wrapper.emitted('click:positive')

    const targetWrapper = wrapper.find('[data-error]')

    expect(targetWrapper.text()).toContain('入力してください。')
    expect(targetWrapper).toMatchSnapshot()
  })

  it('should emit click:positive when z-prompt-dialog emits click:positive if a date is selected', async () => {
    const date = TEST_NOW.toFormat(ISO_DATE_FORMAT)
    await setData(wrapper, { date })
    const zPromptDialogWrapper = wrapper.findComponent({ name: 'ZPromptDialog' })
    zPromptDialogWrapper.vm.$emit('click:positive', new Event('click'))
    await flushPromises()

    const emitted = wrapper.emitted('click:positive')

    expect(emitted).toBeTruthy()
    expect(emitted).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual(TEST_NOW.toFormat(ISO_DATETIME_FORMAT))
  })
})
