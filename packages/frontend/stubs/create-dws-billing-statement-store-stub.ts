/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-billing-statement-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsBillingStatementData
type Store = m.DwsBillingStatementStore

export const createDwsBillingStatementStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsBillingStatementState(),
    ...data
  })
  const getters = m.createDwsBillingStatementGetters(state)
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve(),
    updateCopayCoordination: () => Promise.resolve(),
    updateStatus: () => Promise.resolve()
  }
  const store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useDwsBillingStatementStore').mockReturnValue(store)
  return store
}
