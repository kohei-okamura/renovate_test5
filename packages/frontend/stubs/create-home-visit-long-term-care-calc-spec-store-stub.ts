/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-home-visit-long-term-care-calc-spec-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.HomeVisitLongTermCareCalcSpecData
type Store = m.HomeVisitLongTermCareCalcSpecStore

export const createHomeVisitLongTermCareCalcSpecStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createHomeVisitLongTermCareCalcSpecState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useHomeVisitLongTermCareCalcSpecStore').mockReturnValue(store)
  return store
}
