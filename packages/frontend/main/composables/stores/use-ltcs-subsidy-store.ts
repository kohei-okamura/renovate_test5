/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { UserLtcsSubsidy } from '~/models/user-ltcs-subsidy'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'

export const createLtcsSubsidyState = () => ({
  ltcsSubsidy: undefined as UserLtcsSubsidy | undefined
})

export function useLtcsSubsidyStore () {
  const { $api } = usePlugins()
  const state = reactive(createLtcsSubsidyState())
  const actions = {
    async get (params: LtcsSubsidiesApi.GetParams) {
      const item = await $api.ltcsSubsidies.get(params)
      assign(state, item)
    }
  }
  return createStore({ actions, state })
}

export type LtcsSubsidyData = ReturnType<typeof createLtcsSubsidyState>

export type LtcsSubsidyStore = ReturnType<typeof useLtcsSubsidyStore>

export type LtcsSubsidyState = LtcsSubsidyStore['state']

export const ltcsSubsidyStateKey: InjectionKey<LtcsSubsidyState> = Symbol('ltcsSubsidyState')
