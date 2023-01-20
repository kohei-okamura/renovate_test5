/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-permissions-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.PermissionsData
type Store = m.PermissionsStore

export const createPermissionsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createPermissionsState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve(),
    resolveAbbr: () => ''
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'usePermissionsStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
