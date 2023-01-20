/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-user-ltcs-calc-spec-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.UserLtcsCalcSpecData
type Store = m.UserLtcsCalcSpecStore

export const createUserLtcsCalcSpecStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createUserLtcsCalcSpecState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useUserLtcsCalcSpecStore').mockReturnValue(store)
  return store
}
