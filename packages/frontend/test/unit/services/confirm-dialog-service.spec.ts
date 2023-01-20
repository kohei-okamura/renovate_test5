/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ConfirmDialogService, createConfirmDialogService } from '~/services/confirm-dialog-service'
import { setupVue } from '~~/test/helpers/setup-vue'

describe('services/confirm-dialog-service', () => {
  let dialog: ConfirmDialogService
  beforeAll(() => {
    setupVue()
    dialog = createConfirmDialogService()
  })

  it('should be updated state when show is called', () => {
    const { active, options, show } = dialog
    const opts = {
      color: 'secondary',
      message: 'New Dialog Message',
      negative: 'No',
      positive: 'Yes',
      title: 'New Dialog Title'
    }
    expect(active.value).toBeFalse()
    show(opts)
    expect(opts).toEqual(options.value)
    expect(active.value).toBeTrue()
  })

  it('should be updated state when hide is called', () => {
    const { active, hide } = dialog
    hide()
    expect(active.value).toBeFalse()
  })
})
