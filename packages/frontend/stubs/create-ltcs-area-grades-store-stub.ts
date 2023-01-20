/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-ltcs-area-grades-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.LtcsAreaGradesData
type Store = m.LtcsAreaGradesStore

export const createLtcsAreaGradesStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createLtcsAreaGradesState(),
    ...data
  })
  const getters = {
    ltcsAreaGradeOptions: computed(() => state.ltcsAreaGrades.map(x => ({ text: x.name, value: x.id })))
  }
  const actions = {
    getIndex: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useLtcsAreaGradesStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
