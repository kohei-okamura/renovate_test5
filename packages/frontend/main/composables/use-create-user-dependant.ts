/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { userStoreKey } from '~/composables/stores/use-user-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { UserId } from '~/models/user'

type CreateUserDependantParams = {
  dependant: string
  userId: UserId
  callback: () => Promise<void>
  hash?: string
}

export const useCreateUserDependant = () => {
  const { $alert, $router, $form, $snackbar } = usePlugins()
  const { errors, progress, withAxios } = useAxios()
  const userStore = useInjected(userStoreKey)
  $form.preventUnexpectedUnload()

  const createUserDependant = ({ dependant, userId: id, callback, hash }: CreateUserDependantParams): Promise<void> => {
    return withAxios(
      () => $form.submit(async () => {
        await callback()
        await userStore.get({ id })
        await $router.replace(`/users/${id}${hash ? `#${hash}` : ''}`)
        $snackbar.success(`${dependant}を登録しました。`)
      }),
      error => $alert.error(`${dependant}の登録に失敗しました。`, error.stack)
    )
  }

  return {
    createUserDependant,
    errors,
    progress
  }
}
