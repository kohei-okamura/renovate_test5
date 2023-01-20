/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { HomeHelpServiceCalcSpec } from '~/models/home-help-service-calc-spec'
import { HomeHelpServiceCalcSpecsApi } from '~/services/api/home-help-service-calc-specs-api'

export const createHomeHelpServiceCalcSpecState = () => ({
  homeHelpServiceCalcSpec: undefined as HomeHelpServiceCalcSpec | undefined
})

export function useHomeHelpServiceCalcSpecStore () {
  const { $api } = usePlugins()
  const state = reactive(createHomeHelpServiceCalcSpecState())
  const actions = {
    async get (params: HomeHelpServiceCalcSpecsApi.GetParams) {
      const item = await $api.homeHelpServiceCalcSpecs.get(params)
      assign(state, item)
    }
  }
  return createStore({ actions, state })
}

export type HomeHelpServiceCalcSpecData = ReturnType<typeof createHomeHelpServiceCalcSpecState>

export type HomeHelpServiceCalcSpecStore = ReturnType<typeof useHomeHelpServiceCalcSpecStore>

export type HomeHelpServiceCalcSpecState = HomeHelpServiceCalcSpecStore['state']

export const homeHelpServiceCalcSpecStateKey: InjectionKey<HomeHelpServiceCalcSpecState> = Symbol('homeHelpServiceCalcSpecState')
