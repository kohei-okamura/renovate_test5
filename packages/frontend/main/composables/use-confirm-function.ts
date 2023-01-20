/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { colors } from '~/colors'
import { usePlugins } from '~/composables/use-plugins'
import { RefOrValue, unref } from '~/support/reactive'

type ConfirmFunction = () => Promise<void>

export type UseConfirmFunctionOptions = {
  actionName: string
  callback: () => Promise<void>
  messageOnConfirm: string
  messageOnFailure?: string
  messageOnSuccess: string
  returnTo: string
}

type UseConfirmFunction = <T> (
  target: RefOrValue<T | undefined>,
  options: (x: T) => UseConfirmFunctionOptions
) => ConfirmFunction

export const useConfirmFunction: UseConfirmFunction = (target, options) => {
  const { $alert, $back, $confirm, $snackbar } = usePlugins()
  return async () => {
    const x = unref(target)
    if (x !== undefined) {
      const { actionName, callback, messageOnConfirm, messageOnSuccess, messageOnFailure, returnTo } = options(x)
      const confirmed = await $confirm.show({
        color: colors.critical,
        message: messageOnConfirm,
        positive: actionName
      })
      if (confirmed) {
        try {
          await callback()
          $back(returnTo)
          $snackbar.success(messageOnSuccess)
        } catch (error) {
          $alert.error(messageOnFailure ?? `${actionName}に失敗しました。`)
        }
      }
    }
  }
}
