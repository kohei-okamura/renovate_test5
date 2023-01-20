/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-billing-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsBillingData
type Store = m.LtcsBillingStore

export const createLtcsBillingStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsBillingState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    updateStatus: () => Promise.resolve()
  }
  const getters = m.createLtcsBillingStoreGetters(state)
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useLtcsBillingStore').mockReturnValue(store)
  return store
}
