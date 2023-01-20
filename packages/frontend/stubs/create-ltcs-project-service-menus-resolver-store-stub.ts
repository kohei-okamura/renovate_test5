/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsProjectServiceMenuResolverData
type Store = m.LtcsProjectServiceMenuResolverStore

export const createLtcsProjectServiceMenusResolverStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsProjectServiceMenuResolverState(),
    ...data
  })
  const getters = m.createLtcsProjectServiceMenuResolverStoreGetters(state)
  const actions = {
    update: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useLtcsProjectServiceMenuResolverStore').mockReturnValue(store)
  return store
}
