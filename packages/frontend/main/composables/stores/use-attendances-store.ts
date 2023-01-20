/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Attendance } from '~/models/attendance'
import { Pagination } from '~/models/pagination'
import { AttendancesApi } from '~/services/api/attendances-api'
import { updateReactiveArray } from '~/support/reactive'

export const createAttendancesState = () => ({
  attendances: [] as Attendance[],
  isLoadingAttendances: false,
  pagination: {
    desc: false,
    page: 1,
    itemsPerPage: 10,
    sortBy: 'name'
  } as Pagination,
  queryParams: undefined as AttendancesApi.GetIndexParams | undefined
})

export function useAttendancesStore () {
  const { $api } = usePlugins()
  const state = reactive(createAttendancesState())
  const actions = {
    async getIndex (params: AttendancesApi.GetIndexParams) {
      state.isLoadingAttendances = true
      try {
        const response = await $api.attendances.getIndex(params)
        updateReactiveArray(state.attendances, response.list)
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingAttendances = false
      }
    }
  }
  return createStore({ actions, state })
}

export type AttendancesData = ReturnType<typeof createAttendancesState>

export type AttendancesStore = ReturnType<typeof useAttendancesStore>

export type AttendancesState = AttendancesStore['state']

export const attendancesStoreKey: InjectionKey<AttendancesStore> = Symbol('attendancesStore')

export const attendancesStateKey: InjectionKey<AttendancesState> = Symbol('attendancesState')
