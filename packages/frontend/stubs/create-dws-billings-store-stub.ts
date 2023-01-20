/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-billings-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsBillingsData
type Store = m.DwsBillingsStore

export const createDwsBillingsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsBillingsState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve(),
    resolveDwsBillingName: () => ''
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useDwsBillingsStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
