import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { VisitingCareForPwsdCalcSpec } from '~/models/visiting-care-for-pwsd-calc-spec'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'

export const createVisitingCareForPwsdCalcSpecState = () => ({
  visitingCareForPwsdCalcSpec: undefined as VisitingCareForPwsdCalcSpec | undefined
})

export function useVisitingCareForPwsdCalcSpecStore () {
  const { $api } = usePlugins()
  const state = reactive(createVisitingCareForPwsdCalcSpecState())
  const actions = {
    async get (params: VisitingCareForPwsdCalcSpecsApi.GetParams) {
      const item = await $api.visitingCareForPwsdCalcSpecs.get(params)
      assign(state, item)
    }
  }
  return createStore({ actions, state })
}

export type VisitingCareForPwsdCalcSpecData = ReturnType<typeof createVisitingCareForPwsdCalcSpecState>

export type VisitingCareForPwsdCalcSpecStore = ReturnType<typeof useVisitingCareForPwsdCalcSpecStore>

export type VisitingCareForPwsdCalcSpecState = VisitingCareForPwsdCalcSpecStore['state']

export const visitingCareForPwsdCalcSpecStateKey: InjectionKey<VisitingCareForPwsdCalcSpecState> = Symbol('visitingCareForPwsdCalcSpecState')
