/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-staffs-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.StaffsData
type Store = m.StaffsStore

export const createStaffsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createStaffsState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve(),
    resolveStaffName: () => ''
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useStaffsStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
