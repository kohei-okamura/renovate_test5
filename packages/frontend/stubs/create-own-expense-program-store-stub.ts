/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-own-expense-program-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.OwnExpenseProgramData
type Store = m.OwnExpenseProgramStore

export const createOwnExpenseProgramStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createOwnExpenseProgramState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useOwnExpenseProgramStore').mockReturnValue(store)
  return store
}
