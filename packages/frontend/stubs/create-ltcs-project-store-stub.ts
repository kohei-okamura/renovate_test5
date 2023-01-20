/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-project-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsProjectData
type Store = m.LtcsProjectStore

export const createLtcsProjectStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsProjectState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useLtcsProjectStore').mockReturnValue(store)
  return store
}
