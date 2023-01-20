/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-user-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.UserData
type Store = m.UserStore

export const createUserStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createUserState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve(),
    updateBankAccount: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useUserStore').mockReturnValue(store)
  return store
}
