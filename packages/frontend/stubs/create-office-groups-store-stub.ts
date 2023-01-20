/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import * as m from '~/composables/stores/use-office-groups-store'
import { createStore } from '~/composables/stores/utils'
import { createTree } from '~/models/tree'

type Data = m.OfficeGroupsData
type Store = m.OfficeGroupsStore

export const createOfficeGroupsStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createOfficeGroupsState(),
    ...data
  })
  const getters = {
    officeGroupOptions: computed(() => state.officeGroups.map(x => ({ text: x.name, value: x.id }))),
    officeGroupsTree: computed(() => createTree(state.officeGroups, 'parentOfficeGroupId'))
  }
  const actions = {
    getIndex: () => Promise.resolve(),
    update: () => Promise.resolve()
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useOfficeGroupsStore').mockReturnValue(store)
  jest.spyOn(store, 'getIndex').mockResolvedValue()
  return store
}
