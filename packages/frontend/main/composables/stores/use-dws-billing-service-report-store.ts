/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@vue/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBilling } from '~/models/dws-billing'
import { DwsBillingBundle } from '~/models/dws-billing-bundle'
import { DwsBillingServiceReport } from '~/models/dws-billing-service-report'
import { DwsBillingServiceReportsApi } from '~/services/api/dws-billing-service-reports-api'

export const createDwsBillingServiceReportState = () => ({
  billing: undefined as DwsBilling | undefined,
  bundle: undefined as DwsBillingBundle | undefined,
  report: undefined as DwsBillingServiceReport | undefined
})

export type DwsBillingServiceReportData = ReturnType<typeof createDwsBillingServiceReportState>

export function useDwsBillingServiceReportStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsBillingServiceReportState())
  const actions = {
    async get (params: DwsBillingServiceReportsApi.GetParams) {
      assign(state, await $api.dwsBillingServiceReports.get(params))
    },
    async updateStatus (params: DwsBillingServiceReportsApi.UpdateStatusParams) {
      assign(state, await $api.dwsBillingServiceReports.updateStatus(params))
    }
  }
  return createStore({ actions, state })
}

export type DwsBillingServiceReportStore = ReturnType<typeof useDwsBillingServiceReportStore>

export type DwsBillingServiceReportState = DwsBillingServiceReportStore['state']

export const dwsBillingServiceReportStateKey: InjectionKey<DwsBillingServiceReportState> = Symbol('dwsBillingServiceReportState')

export const dwsBillingServiceReportStoreKey: InjectionKey<DwsBillingServiceReportStore> = Symbol('dwsBillingServiceReportStore')
