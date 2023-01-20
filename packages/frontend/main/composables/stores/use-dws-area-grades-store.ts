/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsAreaGrade } from '~/models/dws-area-grade'
import { updateReactiveArray } from '~/support/reactive'

export const createDwsAreaGradesState = () => ({
  dwsAreaGrades: [] as DwsAreaGrade[],
  isLoadingDwsAreaGrades: false
})

export function useDwsAreaGradesStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsAreaGradesState())
  const actions = {
    async getIndex () {
      state.isLoadingDwsAreaGrades = true
      try {
        const response = await $api.dwsAreaGrades.getIndex({ all: true })
        updateReactiveArray(state.dwsAreaGrades, response.list)
      } finally {
        state.isLoadingDwsAreaGrades = false
      }
    }
  }
  return createStore({ actions, state })
}

export type DwsAreaGradesData = ReturnType<typeof createDwsAreaGradesState>

export type DwsAreaGradesStore = ReturnType<typeof useDwsAreaGradesStore>

export type DwsAreaGradesState = DwsAreaGradesStore['state']

export const dwsAreaGradesStoreKey: InjectionKey<DwsAreaGradesStore> = Symbol('dwsAreaGradesStore')

export const dwsAreaGradesStateKey: InjectionKey<DwsAreaGradesState> = Symbol('dwsAreaGradesState')
