/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsProjectServiceCategoryResolverData
type Store = m.DwsProjectServiceMenuResolverStore

export const createDwsProjectServiceMenuResolverStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsProjectServiceCategoryResolverState(),
    ...data
  })
  const getters = m.createDwsProjectServiceCategoryResolverStoreGetters(state)
  const actions = {
    update: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useDwsProjectServiceMenuResolverStore').mockReturnValue(store)
  return store
}
