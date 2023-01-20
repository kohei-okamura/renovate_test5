/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { LtcsBilling } from '~/models/ltcs-billing'
import { Pagination } from '~/models/pagination'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { updateReactiveArray } from '~/support/reactive'

export const createLtcsBillingsState = () => ({
  ltcsBillings: [] as LtcsBilling[],
  isLoadingLtcsBillings: false,
  pagination: {
    desc: false,
    page: 1,
    itemsPerPage: 10,
    sortBy: 'id'
  } as Pagination,
  queryParams: undefined as LtcsBillingsApi.GetIndexParams | undefined
})

export const useLtcsBillingsStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createLtcsBillingsState())
  const actions = {
    async getIndex (params: LtcsBillingsApi.GetIndexParams) {
      state.isLoadingLtcsBillings = true
      try {
        const response = await $api.ltcsBillings.getIndex(params)
        updateReactiveArray(state.ltcsBillings, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingLtcsBillings = false
      }
    }
  }
  return createStore({ actions, state })
}

export type LtcsBillingsData = ReturnType<typeof createLtcsBillingsState>

export type LtcsBillingsStore = ReturnType<typeof useLtcsBillingsStore>

export type LtcsBillingsState = LtcsBillingsStore['state']

export const ltcsBillingsStoreKey: InjectionKey<LtcsBillingsStore> = Symbol('ltcsBillingsStore')

export const ltcsBillingsStateKey: InjectionKey<LtcsBillingsState> = Symbol('ltcsBillingsState')
