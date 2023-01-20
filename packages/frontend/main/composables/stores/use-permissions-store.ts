/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { PermissionGroup } from '~/models/permission-group'
import { updateReactiveArray } from '~/support/reactive'

export const createPermissionsState = () => ({
  permissionGroups: [] as PermissionGroup[],
  isLoadingPermissions: false
})

export function usePermissionsStore () {
  const { $api } = usePlugins()
  const state = reactive(createPermissionsState())
  const actions = {
    async getIndex () {
      state.isLoadingPermissions = true
      try {
        const response = await $api.permissions.getIndex({ all: true })
        updateReactiveArray(state.permissionGroups, response.list)
      } finally {
        state.isLoadingPermissions = false
      }
    }
  }
  return createStore({ actions, state })
}

export type PermissionsData = ReturnType<typeof createPermissionsState>

export type PermissionsStore = ReturnType<typeof usePermissionsStore>

export type PermissionsState = PermissionsStore['state']

export const permissionsStoreKey: InjectionKey<PermissionsStore> = Symbol('permissionsStore')

export const permissionsStateKey: InjectionKey<PermissionsState> = Symbol('permissionsState')
