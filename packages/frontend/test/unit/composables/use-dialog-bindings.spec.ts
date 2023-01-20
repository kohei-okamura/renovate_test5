/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { mocked } from '@zinger/helpers/testing/mocked'
import { useDialogBindings } from '~/composables/use-dialog-bindings'
import { usePlugins } from '~/composables/use-plugins'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-dialog-bindings', () => {
  const $router = createMockedRouter()
  const plugins = createMockedPlugins({ $router })

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  beforeEach(() => {
    jest.spyOn($router, 'back').mockReturnValue()
  })

  afterEach(() => {
    mocked($router.back).mockReset()
  })

  describe('dialog', () => {
    it('should be ref to false', () => {
      const { dialog } = useDialogBindings()
      expect(dialog).toBeRefTo(false)
    })
  })

  describe('openDialog', () => {
    it('should set dialog = true', () => {
      const { dialog, openDialog } = useDialogBindings()
      expect(dialog.value).toBeFalse()
      openDialog()
      expect(dialog.value).toBeTrue()
      openDialog()
      expect(dialog.value).toBeTrue()
    })
  })

  describe('closeDialog', () => {
    it('should set dialog = false', () => {
      const { dialog, openDialog, closeDialog } = useDialogBindings()
      expect(dialog.value).toBeFalse()

      openDialog()
      expect(dialog.value).toBeTrue()

      closeDialog()
      expect(dialog.value).toBeFalse()

      closeDialog()
      expect(dialog.value).toBeFalse()
    })

    it('should call `$router.back` when router back feature enabled (default)', () => {
      const { openDialog, closeDialog } = useDialogBindings()

      openDialog()
      expect($router.back).not.toHaveBeenCalled()
      closeDialog()

      expect($router.back).toHaveBeenCalledTimes(1)
    })

    it('should not call `$router.back` when router back feature disabled', () => {
      const { disableRouterBack, openDialog, closeDialog } = useDialogBindings()
      disableRouterBack()

      openDialog()
      expect($router.back).not.toHaveBeenCalled()
      closeDialog()

      expect($router.back).not.toHaveBeenCalled()
    })
  })

  describe('toggleDialog', () => {
    it('should set dialog', () => {
      const { dialog, toggleDialog } = useDialogBindings()
      expect(dialog.value).toBeFalse()
      toggleDialog(true)
      expect(dialog.value).toBeTrue()
      toggleDialog(true)
      expect(dialog.value).toBeTrue()
      toggleDialog(false)
      expect(dialog.value).toBeFalse()
    })
  })
})
