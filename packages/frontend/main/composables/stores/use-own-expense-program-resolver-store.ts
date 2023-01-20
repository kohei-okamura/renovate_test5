/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeId } from '~/models/office'
import { OwnExpenseProgram, OwnExpenseProgramId } from '~/models/own-expense-program'
import { computedWithForArray, updateReactiveArray } from '~/support/reactive'

export const createOwnExpenseProgramResolverState = () => ({
  isLoadingOwnExpensePrograms: false,
  ownExpensePrograms: [] as OwnExpenseProgram[]
})

export type OwnExpenseProgramResolverData = ReturnType<typeof createOwnExpenseProgramResolverState>

export const createOwnExpenseProgramResolverStoreGetters = (state: OwnExpenseProgramResolverData) => ({
  resolveOwnExpenseProgramName: computedWithForArray(state, 'ownExpensePrograms', ownExpensePrograms => {
    return (x: OwnExpenseProgram | OwnExpenseProgramId, alternative = '-'): string => {
      return x && typeof x !== 'number'
        ? x.name
        : ownExpensePrograms.find(a => a.id === x)?.name ?? alternative
    }
  }),
  ownExpenseOptions: computed(() => state.ownExpensePrograms.map(x => ({ text: x.name, value: x.id }))),
  ownExpenseOptionsByOffice: computed(() => (officeId: OfficeId) => {
    // 事業所 ID が未設定のもの（すべての事業所で使用できるもの）と事業所 ID が一致するものを返す
    return state.ownExpensePrograms
      .filter(x => !x.officeId || x.officeId === officeId)
      .map(x => ({ text: x.name, value: x.id }))
  })
})

export const useOwnExpenseProgramResolverStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createOwnExpenseProgramResolverState())
  const getters = createOwnExpenseProgramResolverStoreGetters(state)

  const updateOptions = async () => {
    try {
      state.isLoadingOwnExpensePrograms = true
      const response = await $api.ownExpensePrograms.getIndex({ all: true })
      updateReactiveArray(state.ownExpensePrograms, response.list)
    } finally {
      state.isLoadingOwnExpensePrograms = false
    }
  }

  updateOptions()

  const actions = {
    async updateOwnExpenseProgramOptions () {
      await updateOptions()
    }
  }
  return createStore({ actions, getters, state })
}

export type OwnExpenseProgramResolverStore = ReturnType<typeof useOwnExpenseProgramResolverStore>

export type OwnExpenseProgramResolverState = OwnExpenseProgramResolverStore['state']

export const ownExpenseProgramResolverStoreKey: InjectionKey<OwnExpenseProgramResolverStore> = Symbol('OwnExpenseProgramResolverStore')

export const ownExpenseProgramResolverStateKey: InjectionKey<OwnExpenseProgramResolverState> = Symbol('OwnExpenseProgramResolverState')
