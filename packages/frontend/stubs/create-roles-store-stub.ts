/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-roles-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.RolesData
type Store = m.RolesStore

export const createRolesStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createRolesState(),
    ...data
  })
  const getters = {
    roleOptions: computed(() => state.roles.map(x => ({ text: x.name, value: x.id })))
  }
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useRolesStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
