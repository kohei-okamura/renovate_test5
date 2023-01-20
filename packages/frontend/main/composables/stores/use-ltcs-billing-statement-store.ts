/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { LtcsBilling } from '~/models/ltcs-billing'
import { LtcsBillingBundle } from '~/models/ltcs-billing-bundle'
import { LtcsBillingStatement } from '~/models/ltcs-billing-statement'
import { LtcsBillingStatementsApi } from '~/services/api/ltcs-billing-statements-api'

export const createLtcsBillingStatementState = () => ({
  billing: undefined as LtcsBilling | undefined,
  bundle: undefined as LtcsBillingBundle | undefined,
  statement: undefined as LtcsBillingStatement | undefined
})
export type LtcsBillingStatementData = ReturnType<typeof createLtcsBillingStatementState>

export const useLtcsBillingStatementStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createLtcsBillingStatementState())
  const actions = {
    async get (params: LtcsBillingStatementsApi.GetParams) {
      assign(state, await $api.ltcsBillingStatements.get(params))
    },
    async update (params: LtcsBillingStatementsApi.UpdateParams) {
      assign(state, await $api.ltcsBillingStatements.update(params))
    },
    async updateStatus (params: LtcsBillingStatementsApi.GetParams, status: LtcsBillingStatus) {
      const form = { status }
      assign(state, await $api.ltcsBillingStatements.updateStatus({ ...params, form }))
    }
  }
  return createStore({ actions, state })
}
export type LtcsBillingStatementStore = ReturnType<typeof useLtcsBillingStatementStore>
export type LtcsBillingStatementState = LtcsBillingStatementStore['state']

export const ltcsBillingStatementStoreKey: InjectionKey<LtcsBillingStatementStore> = Symbol('ltcsBillingStatementStore')
export const ltcsBillingStatementStateKey: InjectionKey<LtcsBillingStatementState> = Symbol('ltcsBillingStatementState')
