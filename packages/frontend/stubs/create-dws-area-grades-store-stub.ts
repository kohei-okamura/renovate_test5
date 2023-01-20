/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-dws-area-grades-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.DwsAreaGradesData
type Store = m.DwsAreaGradesStore

export const createDwsAreaGradesStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createDwsAreaGradesState(),
    ...data
  })
  const getters = {
    dwsAreaGradeOptions: computed(() => state.dwsAreaGrades.map(x => ({ text: x.name, value: x.id })))
  }
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useDwsAreaGradesStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
