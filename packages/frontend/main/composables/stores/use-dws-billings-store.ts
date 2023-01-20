/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBilling } from '~/models/dws-billing'
import { Pagination } from '~/models/pagination'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { updateReactiveArray } from '~/support/reactive'

export const createDwsBillingsState = () => ({
  dwsBillings: [] as DwsBilling[],
  isLoadingDwsBillings: false,
  pagination: {
    desc: false,
    page: 1,
    itemsPerPage: 10,
    sortBy: 'id'
  } as Pagination,
  queryParams: undefined as DwsBillingsApi.GetIndexParams | undefined
})

export const useDwsBillingsStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createDwsBillingsState())
  const actions = {
    async getIndex (params: DwsBillingsApi.GetIndexParams) {
      state.isLoadingDwsBillings = true
      try {
        const response = await $api.dwsBillings.getIndex(params)
        updateReactiveArray(state.dwsBillings, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingDwsBillings = false
      }
    }
  }
  return createStore({ actions, state })
}

export type DwsBillingsData = ReturnType<typeof createDwsBillingsState>

export type DwsBillingsStore = ReturnType<typeof useDwsBillingsStore>

export type DwsBillingsState = DwsBillingsStore['state']

export const dwsBillingsStoreKey: InjectionKey<DwsBillingsStore> = Symbol('dwsBillingsStore')

export const dwsBillingsStateKey: InjectionKey<DwsBillingsState> = Symbol('dwsBillingsState')
