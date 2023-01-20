/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Pagination } from '~/models/pagination'
import { UserBilling } from '~/models/user-billing'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { updateReactiveArray } from '~/support/reactive'

export const createUserBillingsState = () => ({
  userBillings: [] as UserBilling[],
  isLoadingUserBillings: false,
  pagination: {
    count: 0,
    page: 1,
    itemsPerPage: 1000
  } as Pagination,
  queryParams: undefined as UserBillingsApi.GetIndexParams | undefined
})

export type DwsUserBillingsData = ReturnType<typeof createUserBillingsState>

export function useUserBillingsStore () {
  const { $api } = usePlugins()
  const state = reactive(createUserBillingsState())
  const actions = {
    async getIndex (params: UserBillingsApi.GetIndexParams) {
      state.isLoadingUserBillings = true
      try {
        const response = await $api.userBillings.getIndex(params)
        updateReactiveArray(state.userBillings, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingUserBillings = false
      }
    }
  }
  return createStore({ actions, state })
}

export type UserBillingsData = ReturnType<typeof createUserBillingsState>

export type UserBillingsStore = ReturnType<typeof useUserBillingsStore>

export type UserBillingsState = UserBillingsStore['state']

export const userBillingsStoreKey: InjectionKey<UserBillingsStore> = Symbol('userBillingsStore')

export const userBillingsStateKey: InjectionKey<UserBillingsState> = Symbol('userBillingsState')
