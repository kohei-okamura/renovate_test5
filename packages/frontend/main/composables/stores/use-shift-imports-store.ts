/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'

type UnrefState = {
  errors?: string[]
}

const createShiftImportsState = (): UnrefState => ({
  errors: undefined
})

const createShiftImportsGetters = (state: UnrefState) => ({
  getErrors: computed(() => {
    const errors = state.errors
    return (numberOfMax?: number) => errors && numberOfMax && errors.length > numberOfMax
      ? errors.slice(0, numberOfMax)
      : errors
  })
})

export function useShiftImportsStore () {
  const state = reactive(createShiftImportsState())
  const getters = createShiftImportsGetters(state)
  const actions = {
    updateErrors (errors: UnrefState['errors']) {
      assign(state, { errors })
    },
    resetState () {
      assign(state, createShiftImportsState())
    }
  }
  return createStore({ state, actions, getters })
}

export type ShiftImportsData = ReturnType<typeof createShiftImportsState>

export type ShiftImportsStore = ReturnType<typeof useShiftImportsStore>

export type ShiftImportsState = ShiftImportsStore['state']

export const shiftImportsStoreKey: InjectionKey<ShiftImportsStore> = Symbol('shiftImportsStore')

export const shiftImportsStateKey: InjectionKey<ShiftImportsState> = Symbol('shiftImportsState')
