/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-subsidy-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsSubsidyData
type Store = m.DwsSubsidyStore

export const createDwsSubsidyStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsSubsidyState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useDwsSubsidyStore').mockReturnValue(store)
  return store
}
