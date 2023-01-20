/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Shift } from '~/models/shift'
import { ShiftsApi } from '~/services/api/shifts-api'

export const createShiftState = () => ({
  shift: undefined as Shift | undefined
})

export function useShiftStore () {
  const { $api } = usePlugins()
  const state = reactive(createShiftState())
  const actions = {
    async get (params: ShiftsApi.GetParams) {
      assign(state, await $api.shifts.get(params))
    },
    async update ({ form, id }: Parameters<typeof $api.shifts.update>[0]) {
      assign(state, await $api.shifts.update({ form, id }))
    }
  }
  return createStore({ actions, state })
}

export type ShiftData = ReturnType<typeof createShiftState>

export type ShiftStore = ReturnType<typeof useShiftStore>

export type ShiftState = ShiftStore['state']

export const shiftStoreKey: InjectionKey<ShiftStore> = Symbol('shiftStore')

export const shiftStateKey: InjectionKey<ShiftState> = Symbol('shiftState')
