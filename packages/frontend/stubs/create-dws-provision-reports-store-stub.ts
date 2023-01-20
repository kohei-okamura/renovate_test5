/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-provision-reports-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsProvisionReportsData
type Store = m.DwsProvisionReportsStore

export const createDwsProvisionReportsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsProvisionReportsState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useDwsProvisionReportsStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
