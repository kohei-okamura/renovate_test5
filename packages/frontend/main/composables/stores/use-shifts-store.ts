/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Pagination } from '~/models/pagination'
import { Shift } from '~/models/shift'
import { ShiftsApi } from '~/services/api/shifts-api'
import { updateReactiveArray } from '~/support/reactive'

export const createShiftsState = () => ({
  shifts: [] as Shift[],
  isLoadingShifts: false,
  pagination: {
    desc: false,
    page: 1,
    itemsPerPage: 10,
    sortBy: 'name'
  } as Pagination,
  queryParams: undefined as ShiftsApi.GetIndexParams | undefined
})

export function useShiftsStore () {
  const { $api } = usePlugins()
  const state = reactive(createShiftsState())
  const actions = {
    async getIndex (params: ShiftsApi.GetIndexParams) {
      state.isLoadingShifts = true
      try {
        const response = await $api.shifts.getIndex(params)
        updateReactiveArray(state.shifts, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingShifts = false
      }
    }
  }
  return createStore({ actions, state })
}

export type ShiftsData = ReturnType<typeof createShiftsState>

export type ShiftsStore = ReturnType<typeof useShiftsStore>

export type ShiftsState = ShiftsStore['state']

export const shiftsStoreKey: InjectionKey<ShiftsStore> = Symbol('shiftsStore')

export const shiftsStateKey: InjectionKey<ShiftsState> = Symbol('shiftsState')
