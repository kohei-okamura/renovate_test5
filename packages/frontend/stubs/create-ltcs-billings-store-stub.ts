/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-billings-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsBillingsData
type Store = m.LtcsBillingsStore

export const createLtcsBillingsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsBillingsState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useLtcsBillingsStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
