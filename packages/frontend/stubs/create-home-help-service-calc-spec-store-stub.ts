/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-home-help-service-calc-spec-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.HomeHelpServiceCalcSpecData
type Store = m.HomeHelpServiceCalcSpecStore

export const createHomeHelpServiceCalcSpecStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createHomeHelpServiceCalcSpecState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useHomeHelpServiceCalcSpecStore').mockReturnValue(store)
  return store
}
