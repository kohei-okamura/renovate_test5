/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Attendance } from '~/models/attendance'
import { AttendancesApi } from '~/services/api/attendances-api'

export const createAttendanceState = () => ({
  attendance: undefined as Attendance | undefined
})

export function useAttendanceStore () {
  const { $api } = usePlugins()
  const state = reactive(createAttendanceState())
  const actions = {
    async get (params: AttendancesApi.GetParams) {
      assign(state, await $api.attendances.get(params))
    },
    async update ({ form, id }: Parameters<typeof $api.attendances.update>[0]) {
      assign(state, await $api.attendances.update({ form, id }))
    }
  }
  return createStore({ actions, state })
}

export type AttendanceData = ReturnType<typeof createAttendanceState>

export type AttendanceStore = ReturnType<typeof useAttendanceStore>

export type AttendanceState = AttendanceStore['state']

export const attendanceStoreKey: InjectionKey<AttendanceStore> = Symbol('attendanceStore')

export const attendanceStateKey: InjectionKey<AttendanceState> = Symbol('attendanceState')
