/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-own-expense-program-resolver-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.OwnExpenseProgramResolverData
type Store = m.OwnExpenseProgramResolverStore

export const createOwnExpenseProgramResolverStoreStub = (
  data: Partial<Data> = {}
): Store => {
  const state = reactive({
    ...m.createOwnExpenseProgramResolverState(),
    ...data
  })
  const getters = m.createOwnExpenseProgramResolverStoreGetters(state)
  const actions = {
    updateOwnExpenseProgramOptions: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useOwnExpenseProgramResolverStore').mockReturnValue(store)
  return store
}
