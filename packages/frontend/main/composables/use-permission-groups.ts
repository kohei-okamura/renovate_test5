/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { usePermissionsStore } from '~/composables/stores/use-permissions-store'
import { useBackgroundLoader } from '~/composables/use-background-loader'

export const usePermissionGroups = () => {
  const store = usePermissionsStore()
  useBackgroundLoader(() => store.getIndex())
  return store.state
}
