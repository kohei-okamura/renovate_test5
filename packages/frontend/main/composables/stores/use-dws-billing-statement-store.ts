/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBilling } from '~/models/dws-billing'
import { DwsBillingBundle } from '~/models/dws-billing-bundle'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'

export const createDwsBillingStatementState = () => ({
  billing: undefined as DwsBilling | undefined,
  bundle: undefined as DwsBillingBundle | undefined,
  statement: undefined as DwsBillingStatement | undefined,
  serviceCodeDictionary: undefined as Record<string, string> | undefined
})

export type DwsBillingStatementData = ReturnType<typeof createDwsBillingStatementState>

export const createDwsBillingStatementGetters = (state: DwsBillingStatementData) => ({
  resolveServiceContentAbbr: computed(() => (serviceCode: string) => {
    return state.serviceCodeDictionary && state.serviceCodeDictionary[serviceCode]
  })
})

export function useDwsBillingStatementStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsBillingStatementState())
  const getters = createDwsBillingStatementGetters(state)
  const actions = {
    async get (params: DwsBillingStatementsApi.GetParams) {
      assign(state, await $api.dwsBillingStatements.get(params))
    },
    async update (params: DwsBillingStatementsApi.UpdateParams) {
      assign(state, await $api.dwsBillingStatements.update(params))
    },
    async updateCopayCoordination (params: DwsBillingStatementsApi.UpdateCopayCoordinationParams) {
      assign(state, await $api.dwsBillingStatements.updateCopayCoordination(params))
    },
    async updateStatus (params: DwsBillingStatementsApi.UpdateStatusParams) {
      assign(state, await $api.dwsBillingStatements.updateStatus(params))
    }
  }
  return createStore({ actions, getters, state })
}

export type DwsBillingStatementStore = ReturnType<typeof useDwsBillingStatementStore>

export type DwsBillingStatementState = DwsBillingStatementStore['state']

export const dwsBillingStatementStoreKey: InjectionKey<DwsBillingStatementStore> = Symbol('dwsBillingStatementStore')

export const dwsBillingStatementStateKey: InjectionKey<DwsBillingStatementState> = Symbol('dwsBillingStatementState')
