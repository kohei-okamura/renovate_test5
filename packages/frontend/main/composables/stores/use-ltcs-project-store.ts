/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { LtcsProject } from '~/models/ltcs-project'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'

export const createLtcsProjectState = () => ({
  ltcsProject: undefined as LtcsProject | undefined
})

export function useLtcsProjectStore () {
  const { $api } = usePlugins()
  const state = reactive(createLtcsProjectState())
  const actions = {
    async get (params: LtcsProjectsApi.GetParams) {
      assign(state, await $api.ltcsProjects.get(params))
    },
    async update ({ form, id, userId }: Parameters<typeof $api.ltcsProjects.update>[0]) {
      assign(state, await $api.ltcsProjects.update({ form, id, userId }))
    }
  }
  return createStore({ actions, state })
}

export type LtcsProjectData = ReturnType<typeof createLtcsProjectState>

export type LtcsProjectStore = ReturnType<typeof useLtcsProjectStore>

export type LtcsProjectState = LtcsProjectStore['state']

export const ltcsProjectStoreKey: InjectionKey<LtcsProjectStore> = Symbol('ltcsProjectStore')

export const ltcsProjectStateKey: InjectionKey<LtcsProjectState> = Symbol('ltcsProjectState')
