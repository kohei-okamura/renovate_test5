/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { HomeHelpServiceCalcSpec } from '~/models/home-help-service-calc-spec'
import { HomeVisitLongTermCareCalcSpec } from '~/models/home-visit-long-term-care-calc-spec'
import { Office } from '~/models/office'
import { OfficeGroup } from '~/models/office-group'
import { VisitingCareForPwsdCalcSpec } from '~/models/visiting-care-for-pwsd-calc-spec'
import { OfficesApi } from '~/services/api/offices-api'

export const createOfficeState = () => ({
  homeHelpServiceCalcSpecs: undefined as HomeHelpServiceCalcSpec[] | undefined,
  homeVisitLongTermCareCalcSpecs: undefined as HomeVisitLongTermCareCalcSpec[] | undefined,
  visitingCareForPwsdCalcSpecs: undefined as VisitingCareForPwsdCalcSpec[] | undefined,
  office: undefined as Office | undefined,
  officeGroup: undefined as OfficeGroup | undefined
})

export function useOfficeStore () {
  const { $api } = usePlugins()
  const state = reactive(createOfficeState())
  const actions = {
    async get (params: OfficesApi.GetParams) {
      assign(state, await $api.offices.get(params))
    },
    async update ({ form, id }: Parameters<typeof $api.offices.update>[0]) {
      assign(state, await $api.offices.update({ form, id }))
    }
  }
  return createStore({ actions, state })
}

export type OfficeData = ReturnType<typeof createOfficeState>

export type OfficeStore = ReturnType<typeof useOfficeStore>

export type OfficeState = OfficeStore['state']

export const officeStoreKey: InjectionKey<OfficeStore> = Symbol('officeStore')

export const officeStateKey: InjectionKey<OfficeState> = Symbol('officeState')
