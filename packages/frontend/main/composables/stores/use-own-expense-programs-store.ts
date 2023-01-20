/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { OwnExpenseProgram } from '~/models/own-expense-program'
import { Pagination } from '~/models/pagination'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { updateReactiveArray } from '~/support/reactive'

export const createOwnExpenseProgramsState = () => ({
  ownExpensePrograms: [] as OwnExpenseProgram[],
  isLoadingOwnExpensePrograms: false,
  pagination: {
    desc: false,
    page: 1,
    itemsPerPage: 10,
    sortBy: 'name'
  } as Pagination,
  queryParams: undefined as OwnExpenseProgramsApi.GetIndexParams | undefined
})

export const useOwnExpenseProgramsStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createOwnExpenseProgramsState())
  const actions = {
    async getIndex (params: OwnExpenseProgramsApi.GetIndexParams) {
      state.isLoadingOwnExpensePrograms = true
      try {
        const response = await $api.ownExpensePrograms.getIndex(params)
        updateReactiveArray(state.ownExpensePrograms, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingOwnExpensePrograms = false
      }
    }
  }
  return createStore({ actions, state })
}

export type OwnExpenseProgramsData = ReturnType<typeof createOwnExpenseProgramsState>

export type OwnExpenseProgramsStore = ReturnType<typeof useOwnExpenseProgramsStore>

export type OwnExpenseProgramsState = OwnExpenseProgramsStore['state']

export const ownExpenseProgramsStoreKey: InjectionKey<OwnExpenseProgramsStore> = Symbol('ownExpenseProgramsStore')

export const ownExpenseProgramsStateKey: InjectionKey<OwnExpenseProgramsState> = Symbol('ownExpenseProgramsState')
