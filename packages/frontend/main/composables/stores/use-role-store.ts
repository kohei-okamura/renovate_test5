/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Role } from '~/models/role'
import { RolesApi } from '~/services/api/roles-api'

export const createRoleState = () => ({
  role: undefined as Role | undefined
})

export function useRoleStore () {
  const { $api } = usePlugins()
  const state = reactive(createRoleState())
  const actions = {
    async get (params: RolesApi.GetParams) {
      assign(state, await $api.roles.get(params))
    },
    async update ({ form, id }: Parameters<typeof $api.roles.update>[0]) {
      assign(state, await $api.roles.update({ form, id }))
    }
  }
  return createStore({ actions, state })
}

export type RoleData = ReturnType<typeof createRoleState>

export type RoleStore = ReturnType<typeof useRoleStore>

export type RoleState = RoleStore['state']

export const roleStoreKey: InjectionKey<RoleStore> = Symbol('roleStore')

export const roleStateKey: InjectionKey<RoleState> = Symbol('roleState')
