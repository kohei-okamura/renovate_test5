/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-provision-reports-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsProvisionReportsData
type Store = m.LtcsProvisionReportsStore

export const createLtcsProvisionReportsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsProvisionReportsState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useLtcsProvisionReportsStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
