/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import ZConfirmDialog from '~/components/ui/z-confirm-dialog.vue'
import { ConfirmDialogService, createConfirmDialogService } from '~/services/confirm-dialog-service'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-confirm-dialog.vue', () => {
  const { mount } = setupComponentTest()
  const $confirm = createMock<ConfirmDialogService>({
    ...createConfirmDialogService()
  })

  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    jest.spyOn($confirm, 'resolve')
    jest.spyOn($confirm, 'hide')
    const mocks = {
      $confirm
    }
    wrapper = mount(ZConfirmDialog, { mocks })
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should be close when click negative button', async () => {
    await wrapper.find('[data-button-negative]').trigger('click')
    expect($confirm.resolve).toHaveBeenCalledWith(false)
    expect($confirm.resolve).toHaveBeenCalledTimes(1)
    expect($confirm.hide).toHaveBeenCalledTimes(1)
  })

  it('should be close when click positive button', async () => {
    await wrapper.find('[data-button-positive]').trigger('click')
    expect($confirm.resolve).toHaveBeenCalledWith(true)
    expect($confirm.resolve).toHaveBeenCalledTimes(1)
    expect($confirm.hide).toHaveBeenCalledTimes(1)
  })
})
