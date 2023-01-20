/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Pagination } from '~/models/pagination'
import { User } from '~/models/user'
import { UsersApi } from '~/services/api/users-api'
import { updateReactiveArray } from '~/support/reactive'

export const createUsersState = () => ({
  users: [] as User[],
  isLoadingUsers: false,
  pagination: {
    desc: false,
    page: 1,
    itemsPerPage: 10,
    sortBy: 'name'
  } as Pagination,
  queryParams: undefined as UsersApi.GetIndexParams | undefined
})

export const useUsersStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createUsersState())
  const actions = {
    async getIndex (params: UsersApi.GetIndexParams) {
      state.isLoadingUsers = true
      try {
        const response = await $api.users.getIndex(params)
        updateReactiveArray(state.users, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingUsers = false
      }
    }
  }
  return createStore({ actions, state })
}

export type UsersData = ReturnType<typeof createUsersState>

export type UsersStore = ReturnType<typeof useUsersStore>

export type UsersState = UsersStore['state']

export const usersStoreKey: InjectionKey<UsersStore> = Symbol('usersStore')

export const usersStateKey: InjectionKey<UsersState> = Symbol('usersState')
