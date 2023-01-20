/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-users-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.UsersData
type Store = m.UsersStore

export const createUsersStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createUsersState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useUsersStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
