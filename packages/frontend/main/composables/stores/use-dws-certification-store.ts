/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsCertification } from '~/models/dws-certification'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'

export const createDwsCertificationState = () => ({
  dwsCertification: undefined as DwsCertification | undefined
})

export function useDwsCertificationStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsCertificationState())
  const actions = {
    async get (params: DwsCertificationsApi.GetParams) {
      assign(state, await $api.dwsCertifications.get(params))
    }
  }
  return createStore({ actions, state })
}

export type DwsCertificationData = ReturnType<typeof createDwsCertificationState>

export type DwsCertificationStore = ReturnType<typeof useDwsCertificationStore>

export type DwsCertificationState = DwsCertificationStore['state']

export const dwsCertificationStateKey: InjectionKey<DwsCertificationState> = Symbol('dwsCertificationState')
