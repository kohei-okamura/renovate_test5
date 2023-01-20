/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-staff-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.StaffData
type Store = m.StaffStore

export const createStaffStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createStaffState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    update: () => Promise.resolve(),
    updateBankAccount: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useStaffStore').mockReturnValue(store)
  return store
}
