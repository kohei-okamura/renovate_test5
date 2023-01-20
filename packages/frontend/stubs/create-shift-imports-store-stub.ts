/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-shift-imports-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.ShiftImportsData
type Store = m.ShiftImportsStore

export const createShiftImportsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    getErrors: computed(() => () => false),
    ...data
  })
  const actions = {
    updateErrors: () => Promise.resolve(),
    resetState: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useShiftImportsStore').mockReturnValue(store)
  return store
}
