/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { BankAccount } from '~/models/bank-account'
import { Office } from '~/models/office'
import { Role } from '~/models/role'
import { Staff } from '~/models/staff'
import { StaffsApi } from '~/services/api/staffs-api'

export const createStaffState = () => ({
  bankAccount: undefined as BankAccount | undefined,
  offices: [] as Office[],
  roles: [] as Role[],
  staff: undefined as Staff | undefined
})

export function useStaffStore () {
  const { $api } = usePlugins()
  const state = reactive(createStaffState())
  const actions = {
    async get (params: StaffsApi.GetParams) {
      assign(state, await $api.staffs.get(params))
    },
    async update ({ form, id }: Parameters<typeof $api.staffs.update>[0]) {
      assign(state, await $api.staffs.update({ form, id }))
    },
    async updateBankAccount ({ form }: Parameters<typeof $api.bankAccounts.update>[0]) {
      assign(state, await $api.bankAccounts.update({ form }))
    }
  }
  return createStore({ actions, state })
}

export type StaffData = ReturnType<typeof createStaffState>

export type StaffStore = ReturnType<typeof useStaffStore>

export type StaffState = StaffStore['state']

export const staffStoreKey: InjectionKey<StaffStore> = Symbol('staffStore')

export const staffStateKey: InjectionKey<StaffState> = Symbol('staffState')
