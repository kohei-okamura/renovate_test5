/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { LtcsProvisionReportDigest } from '~/models/ltcs-provision-report-digest'
import { Pagination } from '~/models/pagination'
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { updateReactiveArray } from '~/support/reactive'

export const createLtcsProvisionReportsState = () => ({
  ltcsProvisionReports: [] as LtcsProvisionReportDigest[],
  isLoadingLtcsProvisionReports: false,
  pagination: { page: 1 } as Pagination,
  queryParams: undefined as LtcsProvisionReportsApi.GetIndexParams | undefined
})

export const useLtcsProvisionReportsStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createLtcsProvisionReportsState())
  const actions = {
    async getIndex (params: LtcsProvisionReportsApi.GetIndexParams) {
      if (params.officeId) {
        state.isLoadingLtcsProvisionReports = true
        try {
          // 当面は常に全件取得
          params.all = true
          const response = await $api.ltcsProvisionReports.getIndex(params)
          updateReactiveArray(state.ltcsProvisionReports, response.list)
          state.queryParams = params
        } finally {
          state.isLoadingLtcsProvisionReports = false
        }
      } else {
        // 事業所 ID が未選択の時は検索結果を空にする
        updateReactiveArray(state.ltcsProvisionReports, [])
      }
    }
  }
  return createStore({ actions, state })
}

export type LtcsProvisionReportsData = ReturnType<typeof createLtcsProvisionReportsState>

export type LtcsProvisionReportsStore = ReturnType<typeof useLtcsProvisionReportsStore>

export type LtcsProvisionReportsState = LtcsProvisionReportsStore['state']

export const ltcsProvisionReportsStoreKey: InjectionKey<LtcsProvisionReportsStore> = Symbol('ltcsProvisionReportsStore')

export const ltcsProvisionReportsIndexStoreKey: InjectionKey<LtcsProvisionReportsStore> = Symbol('ltcsProvisionReportsIndexStore')

export const ltcsProvisionReportsStateKey: InjectionKey<LtcsProvisionReportsState> = Symbol('ltcsProvisionReportsState')

export const ltcsProvisionReportsIndexStateKey: InjectionKey<LtcsProvisionReportsState> = Symbol('ltcsProvisionReportsIndexState')
