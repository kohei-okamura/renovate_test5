/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { UserLtcsCalcSpec } from '~/models/user-ltcs-calc-spec'
import { UserLtcsCalcSpecsApi } from '~/services/api/user-ltcs-calc-specs-api'

export const createUserLtcsCalcSpecState = () => ({
  ltcsCalcSpec: undefined as UserLtcsCalcSpec | undefined
})

export function useUserLtcsCalcSpecStore () {
  const { $api } = usePlugins()
  const state = reactive(createUserLtcsCalcSpecState())
  const actions = {
    async get (params: UserLtcsCalcSpecsApi.GetParams) {
      assign(state, await $api.userLtcsCalcSpecs.get(params))
    }
  }
  return createStore({ actions, state })
}

export type UserLtcsCalcSpecData = ReturnType<typeof createUserLtcsCalcSpecState>

export type UserLtcsCalcSpecStore = ReturnType<typeof useUserLtcsCalcSpecStore>

export type UserLtcsCalcSpecState = UserLtcsCalcSpecStore['state']

export const userLtcsCalcSpecStateKey: InjectionKey<UserLtcsCalcSpecState> = Symbol('userLtcsCalcSpecState')
