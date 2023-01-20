/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-provision-report-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsProvisionReportData
type Store = m.DwsProvisionReportStore

export const createDwsProvisionReportStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsProvisionReportState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    getLastPlans: () => Promise.resolve([]),
    update: () => Promise.resolve(),
    updateStatus: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useDwsProvisionReportStore').mockReturnValue(store)
  return store
}
