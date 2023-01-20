/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Pagination } from '~/models/pagination'
import { WithdrawalTransaction } from '~/models/withdrawal-transaction'
import { WithdrawalTransactionsApi } from '~/services/api/withdrawal-transactions-api'
import { updateReactiveArray } from '~/support/reactive'

/**
 * 全銀ファイル（口座振替データ）の配列を作成日（登録日時）の降順に並べ替える
 * ！引数の配列自体を変更するので注意
 *
 * @param withdrawalTransactions 全銀ファイル（口座振替データ）の配列
 * @returns withdrawalTransactions 作成日（登録日時）の降順に並べ替えた全銀ファイル（口座振替データ）の配列
 */
const descendingSortByCreatedAt = (withdrawalTransactions: WithdrawalTransaction[]) => {
  withdrawalTransactions.sort((a, b) => a.createdAt < b.createdAt ? 1 : -1)
  return withdrawalTransactions
}

export const createWithdrawalTransactionsState = () => ({
  withdrawalTransactions: [] as WithdrawalTransaction[],
  isLoadingWithdrawalTransactions: false,
  pagination: {
    desc: true,
    page: 1,
    itemsPerPage: 10
  } as Pagination,
  queryParams: undefined as WithdrawalTransactionsApi.GetIndexParams | undefined
})

export type DwsWithdrawalTransactionsData = ReturnType<typeof createWithdrawalTransactionsState>

export function useWithdrawalTransactionsStore () {
  const { $api } = usePlugins()
  const state = reactive(createWithdrawalTransactionsState())
  const actions = {
    async getIndex (params: WithdrawalTransactionsApi.GetIndexParams) {
      state.isLoadingWithdrawalTransactions = true
      try {
        const response = await $api.withdrawalTransactions.getIndex(params)
        updateReactiveArray(state.withdrawalTransactions, descendingSortByCreatedAt(response.list))
        state.pagination = response.pagination
        state.queryParams = params
      } finally {
        state.isLoadingWithdrawalTransactions = false
      }
    }
  }
  return createStore({ actions, state })
}

export type WithdrawalTransactionsData = ReturnType<typeof createWithdrawalTransactionsState>

export type WithdrawalTransactionsStore = ReturnType<typeof useWithdrawalTransactionsStore>

export type WithdrawalTransactionsState = WithdrawalTransactionsStore['state']

export const withdrawalTransactionsStoreKey: InjectionKey<WithdrawalTransactionsStore> = Symbol('withdrawalTransactionsStore')

export const withdrawalTransactionsStateKey: InjectionKey<WithdrawalTransactionsState> = Symbol('withdrawalTransactionsState')
