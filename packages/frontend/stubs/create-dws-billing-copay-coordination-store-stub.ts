/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-billing-copay-coordination-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsBillingCopayCoordinationData
type Store = m.DwsBillingCopayCoordinationStore

export const createDwsBillingCopayCoordinationStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsBillingCopayCoordinationState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve(),
    updateStatus: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useDwsBillingCopayCoordinationStore').mockReturnValue(store)
  jest.spyOn(store, 'get').mockResolvedValue()
  return store
}
