/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-billing-store'
import { createStore } from '~/composables/stores/utils'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'

type GetResponse = DwsBillingsApi.GetResponse
type Data = m.DwsBillingData
type Store = m.DwsBillingStore

export const createDwsBillingStoreStub = (response: GetResponse, data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsBillingState(),
    ...m.convertResponseToState(response),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    updateStatus: () => Promise.resolve()
  }
  const store = createStore({ actions, state })
  jest.spyOn(m, 'useDwsBillingStore').mockReturnValue(store)
  return store
}
