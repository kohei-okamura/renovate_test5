/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { OwnExpenseProgram } from '~/models/own-expense-program'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'

export const createOwnExpenseProgramState = () => ({
  ownExpenseProgram: undefined as OwnExpenseProgram | undefined
})

export function useOwnExpenseProgramStore () {
  const { $api } = usePlugins()
  const state = reactive(createOwnExpenseProgramState())
  const actions = {
    async get (params: OwnExpenseProgramsApi.GetParams) {
      assign(state, await $api.ownExpensePrograms.get(params))
    },
    async update (params: Parameters<typeof $api.ownExpensePrograms.update>[0]) {
      assign(state, await $api.ownExpensePrograms.update(params))
    }
  }
  return createStore({ actions, state })
}

export type OwnExpenseProgramData = ReturnType<typeof createOwnExpenseProgramState>

export type OwnExpenseProgramStore = ReturnType<typeof useOwnExpenseProgramStore>

export type OwnExpenseProgramState = OwnExpenseProgramStore['state']

export const ownExpenseProgramStoreKey: InjectionKey<OwnExpenseProgramStore> = Symbol('ownExpenseProgramStore')

export const ownExpenseProgramStateKey: InjectionKey<OwnExpenseProgramState> = Symbol('ownExpenseProgramState')
