/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsProject } from '~/models/dws-project'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'

export const createDwsProjectState = () => ({
  dwsProject: undefined as DwsProject | undefined
})

export function useDwsProjectStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsProjectState())
  const actions = {
    async get (params: DwsProjectsApi.GetParams) {
      assign(state, await $api.dwsProjects.get(params))
    },
    async update ({ form, id, userId }: Parameters<typeof $api.dwsProjects.update>[0]) {
      assign(state, await $api.dwsProjects.update({ form, id, userId }))
    }
  }
  return createStore({ actions, state })
}

export type DwsProjectData = ReturnType<typeof createDwsProjectState>

export type DwsProjectStore = ReturnType<typeof useDwsProjectStore>

export type DwsProjectState = DwsProjectStore['state']

export const dwsProjectStoreKey: InjectionKey<DwsProjectStore> = Symbol('dwsProjectStore')

export const dwsProjectStateKey: InjectionKey<DwsProjectState> = Symbol('dwsProjectState')
