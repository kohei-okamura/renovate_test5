/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Ref, ref } from '@nuxtjs/composition-api'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { colors } from '~/colors'
import { useDeleteFunction, UseDeleteFunctionOptions } from '~/composables/use-delete-function'
import { usePlugins } from '~/composables/use-plugins'
import { AlertService } from '~/services/alert-service'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-delete-function', () => {
  const $alert = createMock<AlertService>()
  const $back = createMockedBack()
  const $confirm = createMock<ConfirmDialogService>()
  const $snackbar = createMock<SnackbarService>()
  const messageOnConfirm = '●●を削除します。本当によろしいですか？本当ですか？'
  const messageOnSuccess = '●●を削除しました！'
  const plugins = createMockedPlugins({
    $alert,
    $back,
    $confirm,
    $snackbar
  })
  const returnTo = '/path/to/return-to'

  let f: () => Promise<void>
  let options: UseDeleteFunctionOptions
  let target: Ref<boolean | undefined>

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  beforeEach(() => {
    jest.spyOn($confirm, 'show').mockResolvedValue(true)
    jest.spyOn($snackbar, 'success').mockReturnValue()
    target = ref()
    options = {
      callback: jest.fn().mockResolvedValue(undefined),
      messageOnConfirm,
      messageOnSuccess,
      returnTo
    }
    f = useDeleteFunction(target, () => options)
  })

  afterEach(() => {
    mocked($confirm.show).mockReset()
    mocked($snackbar.success).mockReset()
    $back.mockReset()
  })

  describe('when target.value === undefined', () => {
    it('should not do anything when target is undefined', async () => {
      await f()

      expect($confirm.show).not.toHaveBeenCalled()
      expect(options.callback).not.toHaveBeenCalled()
      expect($snackbar.success).not.toHaveBeenCalled()
      expect($back).not.toHaveBeenCalled()
    })
  })

  describe('when target.value !== undefined', () => {
    beforeEach(() => {
      target.value = false
    })

    it('should display confirm dialog with "messageOnConfirm"', async () => {
      await f()

      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.critical,
        message: messageOnConfirm,
        positive: '削除'
      })
    })

    describe('when does not confirmed', () => {
      beforeEach(() => {
        jest.spyOn($confirm, 'show').mockResolvedValue(false)
      })

      it('should not call callback function when does not confirmed', async () => {
        await f()
        expect(options.callback).not.toHaveBeenCalled()
      })

      it('should not call $back when does not confirmed', async () => {
        await f()
        expect($back).not.toHaveBeenCalled()
      })

      it('should not call $snackbar.success when does not confirmed', async () => {
        await f()
        expect($snackbar.success).not.toHaveBeenCalled()
      })
    })

    describe('when confirmed', () => {
      it('should call callback function when confirmed', async () => {
        await f()
        expect(options.callback).toHaveBeenCalledTimes(1)
      })

      it('should call $back with "returnTo" when confirmed', async () => {
        await f()

        expect($back).toHaveBeenCalledTimes(1)
        expect($back).toHaveBeenCalledWith(returnTo)
      })

      it('should call $snackbar.success when confirmed', async () => {
        await f()

        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith(messageOnSuccess)
      })
    })
  })
})
