/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-subsidy-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsSubsidyData
type Store = m.LtcsSubsidyStore

export const createLtcsSubsidyStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsSubsidyState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useLtcsSubsidyStore').mockReturnValue(store)
  return store
}
