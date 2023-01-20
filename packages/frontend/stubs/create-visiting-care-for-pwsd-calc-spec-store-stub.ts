/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-visiting-care-for-pwsd-calc-spec-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.VisitingCareForPwsdCalcSpecData
type Store = m.VisitingCareForPwsdCalcSpecStore

export const createVisitingCareForPwsdCalcSpecStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createVisitingCareForPwsdCalcSpecState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useVisitingCareForPwsdCalcSpecStore').mockReturnValue(store)
  return store
}
