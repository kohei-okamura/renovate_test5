/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-role-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.RoleData
type Store = m.RoleStore

export const createRoleStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createRoleState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useRoleStore').mockReturnValue(store)
  return store
}
