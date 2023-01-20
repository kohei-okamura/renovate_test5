/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { UserBilling } from '~/models/user-billing'
import { UserBillingsApi } from '~/services/api/user-billings-api'

export const createUserBillingState = () => ({
  userBilling: undefined as UserBilling | undefined
})

export function useUserBillingStore () {
  const { $api } = usePlugins()
  const state = reactive(createUserBillingState())
  const actions = {
    async get (params: UserBillingsApi.GetParams) {
      assign(state, await $api.userBillings.get(params))
    },
    async update (params: Parameters<typeof $api.userBillings.update>[0]) {
      assign(state, await $api.userBillings.update(params))
    }
  }
  return createStore({ actions, state })
}

export type UserBillingData = ReturnType<typeof createUserBillingState>

export type UserBillingStore = ReturnType<typeof useUserBillingStore>

export type UserBillingState = UserBillingStore['state']

export const userBillingStoreKey: InjectionKey<UserBillingStore> = Symbol('userBillingStore')

export const userBillingStateKey: InjectionKey<UserBillingState> = Symbol('userBillingState')
