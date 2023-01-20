/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-setting-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.SettingData
type Store = m.SettingStore

export const createSettingStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createSettingState(),
    ...data
  })
  const actions = {
    get: () => Promise.resolve(),
    create: () => Promise.resolve(),
    update: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, state })
  jest.spyOn(m, 'useSettingStore').mockReturnValue(store)
  return store
}
