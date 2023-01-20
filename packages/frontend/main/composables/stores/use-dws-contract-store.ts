/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Contract } from '~/models/contract'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'

export const createDwsContractState = () => ({
  contract: undefined as Contract | undefined
})

export function useDwsContractStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsContractState())
  const actions = {
    async get (params: DwsContractsApi.GetParams) {
      assign(state, await $api.dwsContracts.get(params))
    },
    async update (params: DwsContractsApi.UpdateParams) {
      assign(state, await $api.dwsContracts.update(params))
    },
    async disable (params: DwsContractsApi.DisableParams) {
      assign(state, await $api.dwsContracts.disable(params))
    }
  }
  return createStore({ actions, state })
}

export type DwsContractData = ReturnType<typeof createDwsContractState>

export type DwsContractStore = ReturnType<typeof useDwsContractStore>

export type DwsContractState = DwsContractStore['state']

export const dwsContractStoreKey: InjectionKey<DwsContractStore> = Symbol('dwsContractStore')

export const dwsContractStateKey: InjectionKey<DwsContractState> = Symbol('dwsContractState')
