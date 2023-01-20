/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-billing-statement-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsBillingStatementData
type Store = m.LtcsBillingStatementStore

export const createLtcsBillingStatementStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsBillingStatementState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve(),
    updateStatus: () => Promise.resolve()
  }
  const store = createStore({ actions, state })
  jest.spyOn(m, 'useLtcsBillingStatementStore').mockReturnValue(store)
  return store
}
