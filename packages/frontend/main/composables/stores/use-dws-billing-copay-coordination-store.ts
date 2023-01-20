/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBilling } from '~/models/dws-billing'
import { DwsBillingBundle } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordination } from '~/models/dws-billing-copay-coordination'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'

export const createDwsBillingCopayCoordinationState = () => ({
  billing: undefined as DwsBilling | undefined,
  bundle: undefined as DwsBillingBundle | undefined,
  copayCoordination: undefined as DwsBillingCopayCoordination | undefined
})

export type DwsBillingCopayCoordinationData = ReturnType<typeof createDwsBillingCopayCoordinationState>

export function useDwsBillingCopayCoordinationStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsBillingCopayCoordinationState())
  const actions = {
    async get (params: DwsBillingCopayCoordinationsApi.GetParams) {
      assign(state, await $api.dwsBillingCopayCoordinations.get(params))
    },
    async updateStatus (params: DwsBillingStatementsApi.UpdateStatusParams) {
      assign(state, await $api.dwsBillingCopayCoordinations.updateStatus(params))
    },
    async update (params: DwsBillingCopayCoordinationsApi.UpdateParams) {
      assign(state, await $api.dwsBillingCopayCoordinations.update(params))
    }
  }
  return createStore({ actions, state })
}

export type DwsBillingCopayCoordinationStore = ReturnType<typeof useDwsBillingCopayCoordinationStore>

export type DwsBillingCopayCoordinationState = DwsBillingCopayCoordinationStore['state']

export const dwsBillingCopayCoordinationStateKey: InjectionKey<DwsBillingCopayCoordinationState> = Symbol('dwsBillingCopayCoordinationState')

export const dwsBillingCopayCoordinationStoreKey: InjectionKey<DwsBillingCopayCoordinationStore> = Symbol('dwsBillingCopayCoordinationStore')
