/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsProvisionReportDigest } from '~/models/dws-provision-report-digest'
import { Pagination } from '~/models/pagination'
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import { updateReactiveArray } from '~/support/reactive'

export const createDwsProvisionReportsState = () => ({
  dwsProvisionReports: [] as DwsProvisionReportDigest[],
  isLoadingDwsProvisionReports: false,
  pagination: { page: 1 } as Pagination,
  queryParams: undefined as DwsProvisionReportsApi.GetIndexParams | undefined
})

export const useDwsProvisionReportsStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createDwsProvisionReportsState())
  const actions = {
    async getIndex (params: DwsProvisionReportsApi.GetIndexParams) {
      if (params.officeId) {
        state.isLoadingDwsProvisionReports = true
        try {
          // 当面は常に全件取得
          params.all = true
          const response = await $api.dwsProvisionReports.getIndex(params)
          updateReactiveArray(state.dwsProvisionReports, response.list)
          state.queryParams = params
        } finally {
          state.isLoadingDwsProvisionReports = false
        }
      } else {
        // 事業所 ID が未選択の時は検索結果を空にする
        updateReactiveArray(state.dwsProvisionReports, [])
      }
    }
  }
  return createStore({ actions, state })
}

export type DwsProvisionReportsData = ReturnType<typeof createDwsProvisionReportsState>

export type DwsProvisionReportsStore = ReturnType<typeof useDwsProvisionReportsStore>

export type DwsProvisionReportsState = DwsProvisionReportsStore['state']

export const dwsProvisionReportsStoreKey: InjectionKey<DwsProvisionReportsStore> = Symbol('dwsProvisionReportsStore')

export const dwsProvisionReportsIndexStoreKey: InjectionKey<DwsProvisionReportsStore> = Symbol('dwsProvisionReportsIndexStore')

export const dwsProvisionReportsStateKey: InjectionKey<DwsProvisionReportsState> = Symbol('dwsProvisionReportsState')

export const dwsProvisionReportsIndexStateKey: InjectionKey<DwsProvisionReportsState> = Symbol('dwsProvisionReportsIndexState')
