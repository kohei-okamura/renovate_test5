/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { BankAccount } from '~/models/bank-account'
import { Contract } from '~/models/contract'
import { DwsCertification } from '~/models/dws-certification'
import { DwsProject } from '~/models/dws-project'
import { LtcsInsCard } from '~/models/ltcs-ins-card'
import { LtcsProject } from '~/models/ltcs-project'
import { User } from '~/models/user'
import { UserDwsCalcSpec } from '~/models/user-dws-calc-spec'
import { UserDwsSubsidy } from '~/models/user-dws-subsidy'
import { UserLtcsCalcSpec } from '~/models/user-ltcs-calc-spec'
import { UserLtcsSubsidy } from '~/models/user-ltcs-subsidy'
import { UsersApi } from '~/services/api/users-api'

export const createUserState = () => ({
  bankAccount: undefined as BankAccount | undefined,
  contracts: [] as Contract[],
  dwsCalcSpecs: [] as UserDwsCalcSpec[],
  dwsCertifications: [] as DwsCertification[],
  dwsProjects: [] as DwsProject[],
  dwsSubsidies: [] as UserDwsSubsidy[],
  ltcsCalcSpecs: [] as UserLtcsCalcSpec[],
  ltcsInsCards: [] as LtcsInsCard[],
  ltcsProjects: [] as LtcsProject[],
  ltcsSubsidies: [] as UserLtcsSubsidy[],
  user: undefined as User | undefined
})

export function useUserStore () {
  const { $api } = usePlugins()
  const state = reactive(createUserState())
  const actions = {
    async get (params: UsersApi.GetParams) {
      assign(state, await $api.users.get(params))
    },
    async update ({ form, id }: Parameters<typeof $api.users.update>[0]) {
      assign(state, await $api.users.update({ form, id }))
    },
    async updateBankAccount ({ form }: Parameters<typeof $api.bankAccounts.update>[0]) {
      assign(state, await $api.bankAccounts.update({ form }))
    }
  }
  return createStore({ actions, state })
}

export type UserData = ReturnType<typeof createUserState>

export type UserStore = ReturnType<typeof useUserStore>

export type UserState = UserStore['state']

export const userStoreKey: InjectionKey<UserStore> = Symbol('userStore')

export const userStateKey: InjectionKey<UserState> = Symbol('userState')
