/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Office } from '~/models/office'
import { Pagination } from '~/models/pagination'
import { OfficesApi } from '~/services/api/offices-api'
import { updateReactiveArray } from '~/support/reactive'

export const createOfficesState = () => ({
  offices: [] as Office[],
  isLoadingOffices: false,
  pagination: {
    desc: false,
    page: 1,
    itemsPerPage: 10,
    sortBy: 'name'
  } as Pagination,
  queryParams: undefined as OfficesApi.GetIndexParams | undefined
})

export const useOfficesStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createOfficesState())
  const actions = {
    async getIndex (params: OfficesApi.GetIndexParams) {
      state.isLoadingOffices = true
      try {
        const response = await $api.offices.getIndex(params)
        updateReactiveArray(state.offices, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingOffices = false
      }
    }
  }
  return createStore({ actions, state })
}

export type OfficesData = ReturnType<typeof createOfficesState>

export type OfficesStore = ReturnType<typeof useOfficesStore>

export type OfficesState = OfficesStore['state']

export const officesStoreKey: InjectionKey<OfficesStore> = Symbol('officesStore')

export const officesStateKey: InjectionKey<OfficesState> = Symbol('officesState')
