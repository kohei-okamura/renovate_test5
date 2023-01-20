/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-user-dws-calc-spec-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.UserDwsCalcSpecData
type Store = m.UserDwsCalcSpecStore

export const createUserDwsCalcSpecStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createUserDwsCalcSpecState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useUserDwsCalcSpecStore').mockReturnValue(store)
  return store
}
