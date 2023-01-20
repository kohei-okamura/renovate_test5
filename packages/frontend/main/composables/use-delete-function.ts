/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { useConfirmFunction } from '~/composables/use-confirm-function'
import { RefOrValue } from '~/support/reactive'

type DeleteFunction = () => Promise<void>

export type UseDeleteFunctionOptions = {
  callback: () => Promise<void>
  messageOnConfirm: string
  messageOnSuccess: string
  returnTo: string
}

type UseDeleteFunction = <T> (
  target: RefOrValue<T | undefined>,
  options: (x: T) => UseDeleteFunctionOptions
) => DeleteFunction

export const useDeleteFunction: UseDeleteFunction = (target, options) => {
  return useConfirmFunction(target, x => ({ ...options(x), actionName: '削除' }))
}
