/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Contract } from '~/models/contract'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'

export const createLtcsContractState = () => ({
  contract: undefined as Contract | undefined
})

export function useLtcsContractStore () {
  const { $api } = usePlugins()
  const state = reactive(createLtcsContractState())
  const actions = {
    async get (params: LtcsContractsApi.GetParams) {
      assign(state, await $api.ltcsContracts.get(params))
    },
    async update (params: LtcsContractsApi.UpdateParams) {
      assign(state, await $api.ltcsContracts.update(params))
    },
    async disable (params: LtcsContractsApi.DisableParams) {
      assign(state, await $api.ltcsContracts.disable(params))
    }
  }
  return createStore({ actions, state })
}

export type LtcsContractData = ReturnType<typeof createLtcsContractState>

export type LtcsContractStore = ReturnType<typeof useLtcsContractStore>

export type LtcsContractState = LtcsContractStore['state']

export const ltcsContractStoreKey: InjectionKey<LtcsContractStore> = Symbol('ltcsContractStore')

export const ltcsContractStateKey: InjectionKey<LtcsContractState> = Symbol('ltcsContractState')
