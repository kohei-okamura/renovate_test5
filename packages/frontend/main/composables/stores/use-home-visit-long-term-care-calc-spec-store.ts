/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { HomeVisitLongTermCareCalcSpec } from '~/models/home-visit-long-term-care-calc-spec'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'

export const createHomeVisitLongTermCareCalcSpecState = () => ({
  homeVisitLongTermCareCalcSpec: undefined as HomeVisitLongTermCareCalcSpec | undefined
})

export function useHomeVisitLongTermCareCalcSpecStore () {
  const { $api } = usePlugins()
  const state = reactive(createHomeVisitLongTermCareCalcSpecState())
  const actions = {
    async get (params: HomeVisitLongTermCareCalcSpecsApi.GetParams) {
      const item = await $api.homeVisitLongTermCareCalcSpecs.get(params)
      assign(state, item)
    }
  }
  return createStore({ actions, state })
}

export type HomeVisitLongTermCareCalcSpecData = ReturnType<typeof createHomeVisitLongTermCareCalcSpecState>

export type HomeVisitLongTermCareCalcSpecStore = ReturnType<typeof useHomeVisitLongTermCareCalcSpecStore>

export type HomeVisitLongTermCareCalcSpecState = HomeVisitLongTermCareCalcSpecStore['state']

export const homeVisitLongTermCareCalcSpecStateKey: InjectionKey<HomeVisitLongTermCareCalcSpecState> = Symbol('homeVisitLongTermCareCalcSpecState')
