/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { userStoreKey } from '~/composables/stores/use-user-store'
import { useDeleteFunction } from '~/composables/use-delete-function'
import { useInjected } from '~/composables/use-injected'
import { UserId } from '~/models/user'
import { RefOrValue } from '~/support/reactive'

type DeleteUserDependantParams = {
  dependant: string
  userId: UserId
  target: RefOrValue<{ id: number } & Dictionary | undefined>
  callback: ({ id, userId }: { id: number, userId: UserId }) => Promise<void>
  hash?: string
}

export const useDeleteUserDependant = () => {
  const userStore = useInjected(userStoreKey)

  const deleteUserDependant = ({ dependant, userId, target, callback: fn, hash }: DeleteUserDependantParams) => {
    return useDeleteFunction(target, ({ id }) => ({
      messageOnConfirm: `${dependant}を削除します。\n\n本当によろしいですか？`,
      messageOnSuccess: `${dependant}を削除しました。`,
      returnTo: `/users/${userId}${hash ? `#${hash}` : ''}`,
      callback: async () => {
        await fn({ id, userId })
        await userStore.get({ id: userId })
      }
    }))
  }

  return {
    deleteUserDependant
  }
}
