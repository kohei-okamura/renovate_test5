/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { officeStoreKey } from '~/composables/stores/use-office-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeId } from '~/models/office'

type CreateOfficeDependantParams = {
  dependant: string
  officeId: OfficeId
  callback: () => Promise<void>
  hash?: string
}

export const useCreateOfficeDependant = () => {
  const { $alert, $back, $form, $snackbar } = usePlugins()
  const { errors, progress, withAxios } = useAxios()
  const officeStore = useInjected(officeStoreKey)
  $form.preventUnexpectedUnload()

  const createOfficeDependant = (
    { dependant, officeId: id, callback, hash }: CreateOfficeDependantParams
  ): Promise<void> => {
    return withAxios(
      () => $form.submit(async () => {
        await callback()
        await officeStore.get({ id })
        await $back(`/offices/${id}${hash ? `#${hash}` : ''}`)
        $snackbar.success(`${dependant}を登録しました。`)
      }),
      error => $alert.error(`${dependant}の登録に失敗しました。`, error.stack)
    )
  }

  return {
    createOfficeDependant,
    errors,
    progress
  }
}
