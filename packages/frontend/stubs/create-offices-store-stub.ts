/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-offices-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.OfficesData
type Store = m.OfficesStore

export const createOfficesStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createOfficesState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve(),
    resolveOfficeAbbr: () => ''
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useOfficesStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
