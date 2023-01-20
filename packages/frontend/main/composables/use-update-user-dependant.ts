/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { catchErrorStack } from '~/composables/catch-error-stack'
import { userStoreKey } from '~/composables/stores/use-user-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { UserId } from '~/models/user'

type UpdateUserDependantParams = {
  dependant: string
  userId: UserId
  callback: () => Promise<void>
  hash?: string
}

export const useUpdateUserDependant = () => {
  const { $alert, $form, $router, $snackbar } = usePlugins()
  const { errors, progress, withAxios } = useAxios()
  const userStore = useInjected(userStoreKey)
  $form.preventUnexpectedUnload()

  const updateUserDependant = ({ dependant, userId: id, callback, hash }: UpdateUserDependantParams): Promise<void> => {
    return withAxios(
      () => $form.submit(async () => {
        await callback()
        await userStore.get({ id })
        await catchErrorStack(() => $router.replace(`/users/${id}${hash ? `#${hash}` : ''}`))
        $snackbar.success(`${dependant}を編集しました。`)
      }),
      error => $alert.error(`${dependant}の編集に失敗しました。`, error.stack)
    )
  }

  return {
    updateUserDependant,
    errors,
    progress
  }
}
