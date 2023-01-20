/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { UserDwsSubsidy } from '~/models/user-dws-subsidy'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'

export const createDwsSubsidyState = () => ({
  dwsSubsidy: undefined as UserDwsSubsidy | undefined
})

export function useDwsSubsidyStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsSubsidyState())
  const actions = {
    async get (params: DwsSubsidiesApi.GetParams) {
      assign(state, await $api.dwsSubsidies.get(params))
    },
    async update (params: Parameters<typeof $api.dwsSubsidies.update>[0]) {
      assign(state, await $api.dwsSubsidies.update(params))
    }
  }
  return createStore({ actions, state })
}

export type DwsSubsidyData = ReturnType<typeof createDwsSubsidyState>

export type DwsSubsidyStore = ReturnType<typeof useDwsSubsidyStore>

export type DwsSubsidyState = DwsSubsidyStore['state']

export const dwsSubsidyStoreKey: InjectionKey<DwsSubsidyStore> = Symbol('dwsSubsidyStore')

export const dwsSubsidyStateKey: InjectionKey<DwsSubsidyState> = Symbol('dwsSubsidyState')
