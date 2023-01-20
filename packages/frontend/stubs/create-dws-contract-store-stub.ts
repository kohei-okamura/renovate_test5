/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-contract-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsContractData
type Store = m.DwsContractStore

export const createDwsContractStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsContractState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve(),
    disable: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useDwsContractStore').mockReturnValue(store)
  return store
}
