/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-certification-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsCertificationData
type Store = m.DwsCertificationStore

export const createDwsCertificationStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsCertificationState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useDwsCertificationStore').mockReturnValue(store)
  return store
}
