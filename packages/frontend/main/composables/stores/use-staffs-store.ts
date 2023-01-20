/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Pagination } from '~/models/pagination'
import { Staff } from '~/models/staff'
import { StaffsApi } from '~/services/api/staffs-api'
import { updateReactiveArray } from '~/support/reactive'

export const createStaffsState = () => ({
  staffs: [] as Staff[],
  isLoadingStaffs: false,
  pagination: {
    desc: false,
    page: 1,
    itemsPerPage: 10,
    sortBy: 'name'
  } as Pagination,
  queryParams: undefined as StaffsApi.GetIndexParams | undefined
})

export const useStaffsStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createStaffsState())
  const actions = {
    async getIndex (params: StaffsApi.GetIndexParams) {
      state.isLoadingStaffs = true
      try {
        const response = await $api.staffs.getIndex(params)
        updateReactiveArray(state.staffs, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingStaffs = false
      }
    }
  }
  return createStore({ actions, state })
}

export type StaffsData = ReturnType<typeof createStaffsState>

export type StaffsStore = ReturnType<typeof useStaffsStore>

export type StaffsState = StaffsStore['state']

export const staffsStoreKey: InjectionKey<StaffsStore> = Symbol('staffsStore')

export const staffsStateKey: InjectionKey<StaffsState> = Symbol('staffsState')
