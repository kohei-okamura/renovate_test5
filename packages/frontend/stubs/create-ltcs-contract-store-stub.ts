/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-contract-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsContractData
type Store = m.LtcsContractStore

export const createLtcsContractStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsContractState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve(),
    disable: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useLtcsContractStore').mockReturnValue(store)
  return store
}
