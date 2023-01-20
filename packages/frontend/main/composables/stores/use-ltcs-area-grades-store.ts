/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { LtcsAreaGrade } from '~/models/ltcs-area-grade'
import { updateReactiveArray } from '~/support/reactive'

export const createLtcsAreaGradesState = () => ({
  ltcsAreaGrades: [] as LtcsAreaGrade[],
  isLoadingLtcsAreaGrades: false
})

export function useLtcsAreaGradesStore () {
  const { $api } = usePlugins()
  const state = reactive(createLtcsAreaGradesState())
  const actions = {
    async getIndex () {
      state.isLoadingLtcsAreaGrades = true
      try {
        const response = await $api.ltcsAreaGrades.getIndex({ all: true })
        updateReactiveArray(state.ltcsAreaGrades, response.list)
      } finally {
        state.isLoadingLtcsAreaGrades = false
      }
    }
  }
  return createStore({ actions, state })
}

export type LtcsAreaGradesData = ReturnType<typeof createLtcsAreaGradesState>

export type LtcsAreaGradesStore = ReturnType<typeof useLtcsAreaGradesStore>

export type LtcsAreaGradesState = LtcsAreaGradesStore['state']

export const ltcsAreaGradesStoreKey: InjectionKey<LtcsAreaGradesStore> = Symbol('ltcsAreaGradesStore')

export const ltcsAreaGradesStateKey: InjectionKey<LtcsAreaGradesState> = Symbol('ltcsAreaGradesState')
