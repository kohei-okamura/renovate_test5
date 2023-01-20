/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, InjectionKey, reactive } from '@nuxtjs/composition-api'
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { assign } from '@zinger/helpers'
import { Seq } from 'immutable'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DateString } from '~/models/date'
import { Job } from '~/models/job'
import { LtcsBilling, LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundle } from '~/models/ltcs-billing-bundle'
import { LtcsBillingStatement } from '~/models/ltcs-billing-statement'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'

const statusKeys = LtcsBillingStatus.keysMap()
type StatusKey = typeof statusKeys extends Record<LtcsBillingStatus, infer U>
  ? (U extends 'disabled' ? never : U)
  : never

export const createLtcsBillingState = () => ({
  billing: undefined as LtcsBilling | undefined,
  bundles: [] as LtcsBillingBundle[],
  statements: [] as LtcsBillingStatement[],
  job: undefined as Job | undefined
})
export type LtcsBillingData = ReturnType<typeof createLtcsBillingState>

export type LtcsBillingStoreStatusAggregate = Record<DateString, Record<StatusKey | 'total', number>>

export const createLtcsBillingStoreGetters = (state: LtcsBillingData) => {
  // サービス提供年月の一覧
  const providedInList = computed(() => {
    const { bundles } = state
    return bundles.map(x => x.providedIn).sort().reverse()
  })

  // サービス提供年月別の明細書（内部処理用）
  const groupedStatementCollection = computed(() => {
    const { bundles, statements } = state
    const map = Object.fromEntries(bundles.map(x => [x.id, x.providedIn]))
    return Seq(statements).groupBy(x => map[x.bundleId])
  })

  // サービス提供年月別の明細書
  const groupedStatements = computed(() => {
    return groupedStatementCollection.value.mapEntries(([key, xs]) => {
      return [key, xs.sortBy(x => x.user.name.phoneticDisplayName).toArray()]
    }).toObject()
  })

  // サービス提供年月・状態ごとの明細書の件数
  const statusAggregate = computed<LtcsBillingStoreStatusAggregate>(() => {
    const aggregate = groupedStatementCollection.value.mapEntries(([providedIn, xs]) => [providedIn, {
      ...xs.countBy(x => statusKeys[x.status]).toObject() as Record<StatusKey, number>,
      total: xs.count()
    }])
    return aggregate.toObject()
  })

  // 明細書の有無
  const hasStatements = computed(() => state.statements.length > 0)

  return {
    groupedStatements,
    hasStatements,
    providedInList,
    statusAggregate
  }
}

export const useLtcsBillingStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createLtcsBillingState())
  const getters = createLtcsBillingStoreGetters(state)
  const actions = {
    async get (params: LtcsBillingsApi.GetParams) {
      assign(state, await $api.ltcsBillings.get(params))
    },
    async updateStatus (id: LtcsBillingId, status: LtcsBillingStatus) {
      const form = { status }
      assign(state, await $api.ltcsBillings.updateStatus({ id, form }))
    }
  }
  return createStore({ actions, getters, state })
}

export type LtcsBillingStore = ReturnType<typeof useLtcsBillingStore>

export type LtcsBillingState = LtcsBillingStore['state']

export const ltcsBillingStoreKey: InjectionKey<LtcsBillingStore> = Symbol('ltcsBillingStore')

export const ltcsBillingStateKey: InjectionKey<LtcsBillingState> = Symbol('ltcsBillingState')
