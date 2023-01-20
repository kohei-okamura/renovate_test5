/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { catchErrorStack } from '~/composables/catch-error-stack'
import { officeStoreKey } from '~/composables/stores/use-office-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeId } from '~/models/office'

type UpdateOfficeDependantParams = {
  dependant: string
  officeId: OfficeId
  callback: () => Promise<void>
  hash?: string
}

export const useUpdateOfficeDependant = () => {
  const { $alert, $form, $router, $snackbar } = usePlugins()
  const { errors, progress, withAxios } = useAxios()
  const officeStore = useInjected(officeStoreKey)
  $form.preventUnexpectedUnload()

  const updateOfficeDependant = (
    { dependant, officeId: id, callback, hash }: UpdateOfficeDependantParams
  ): Promise<void> => {
    return withAxios(
      () => $form.submit(async () => {
        await callback()
        await officeStore.get({ id })
        await catchErrorStack(() => $router.replace(`/offices/${id}${hash ? `#${hash}` : ''}`))
        $snackbar.success(`${dependant}を編集しました。`)
      }),
      error => $alert.error(`${dependant}の編集に失敗しました。`, error.stack)
    )
  }

  return {
    updateOfficeDependant,
    errors,
    progress
  }
}
