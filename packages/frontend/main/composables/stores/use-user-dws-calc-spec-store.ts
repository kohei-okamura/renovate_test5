/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { UserDwsCalcSpec } from '~/models/user-dws-calc-spec'
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'

export const createUserDwsCalcSpecState = () => ({
  dwsCalcSpec: undefined as UserDwsCalcSpec | undefined
})

export function useUserDwsCalcSpecStore () {
  const { $api } = usePlugins()
  const state = reactive(createUserDwsCalcSpecState())
  const actions = {
    async get (params: UserDwsCalcSpecsApi.GetParams) {
      assign(state, await $api.userDwsCalcSpecs.get(params))
    }
  }
  return createStore({ actions, state })
}

export type UserDwsCalcSpecData = ReturnType<typeof createUserDwsCalcSpecState>

export type UserDwsCalcSpecStore = ReturnType<typeof useUserDwsCalcSpecStore>

export type UserDwsCalcSpecState = UserDwsCalcSpecStore['state']

export const userDwsCalcSpecStateKey: InjectionKey<UserDwsCalcSpecState> = Symbol('userDwsCalcSpecState')
