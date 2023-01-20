/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-own-expense-programs-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.OwnExpenseProgramsData
type Store = m.OwnExpenseProgramsStore

export const createOwnExpenseProgramsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createOwnExpenseProgramsState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useOwnExpenseProgramsStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
