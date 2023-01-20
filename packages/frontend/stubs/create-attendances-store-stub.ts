/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-attendances-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.AttendancesData
type Store = m.AttendancesStore

export const createAttendancesStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createAttendancesState(),
    ...data
  })
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useAttendancesStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
