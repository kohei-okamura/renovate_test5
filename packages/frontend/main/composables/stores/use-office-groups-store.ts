/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeGroup } from '~/models/office-group'
import { createTree } from '~/models/tree'
import { updateReactiveArray } from '~/support/reactive'

export const createOfficeGroupsState = () => ({
  officeGroups: [] as OfficeGroup[],
  isLoadingOfficeGroups: false
})

export function useOfficeGroupsStore () {
  const { $api } = usePlugins()
  const state = reactive(createOfficeGroupsState())
  const getters = {
    officeGroupOptions: computed(() => state.officeGroups.map(x => ({ text: x.name, value: x.id }))),
    officeGroupsTree: computed(() => createTree(state.officeGroups, 'parentOfficeGroupId'))
  }
  const actions = {
    async getIndex () {
      state.isLoadingOfficeGroups = true
      try {
        const response = await $api.officeGroups.getIndex({ all: true })
        updateReactiveArray(state.officeGroups, response.list)
      } finally {
        state.isLoadingOfficeGroups = false
      }
    },
    async update ({ form, id }: Parameters<typeof $api.officeGroups.update>[0]) {
      const response = await $api.officeGroups.update({ form, id })
      assign(state, { officeGroups: response.list })
    }
  }
  return createStore({ actions, getters, state })
}

export type OfficeGroupsData = ReturnType<typeof createOfficeGroupsState>

export type OfficeGroupsStore = ReturnType<typeof useOfficeGroupsStore>

export type OfficeGroupsState = OfficeGroupsStore['state']

export const officeGroupsStoreKey: InjectionKey<OfficeGroupsStore> = Symbol('officeGroupsStore')

export const officeGroupsStateKey: InjectionKey<OfficeGroupsState> = Symbol('officeGroupsState')
