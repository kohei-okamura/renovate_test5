/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Role } from '~/models/role'
import { updateReactiveArray } from '~/support/reactive'

export const createRolesState = () => ({
  roles: [] as Role[],
  isLoadingRoles: false
})

export function useRolesStore () {
  const { $api } = usePlugins()
  const state = reactive(createRolesState())
  const actions = {
    async getIndex () {
      state.isLoadingRoles = true
      try {
        const response = await $api.roles.getIndex({ all: true })
        updateReactiveArray(state.roles, response.list)
      } finally {
        state.isLoadingRoles = false
      }
    }
  }
  return createStore({ actions, state })
}

export type RolesData = ReturnType<typeof createRolesState>

export type RolesStore = ReturnType<typeof useRolesStore>

export type RolesState = RolesStore['state']

export const rolesStoreKey: InjectionKey<RolesStore> = Symbol('rolesStore')

export const rolesStateKey: InjectionKey<RolesState> = Symbol('rolesState')
