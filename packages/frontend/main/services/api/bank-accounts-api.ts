/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'
import { AxiosInstance } from 'axios'
import { BankAccount } from '~/models/bank-account'
import { api, Api } from '~/services/api/core'

/**
 * 銀行口座 API.
 */
export namespace BankAccountsApi {
  export type Form = {
    userId?: number
    staffId?: number
    bankName?: string
    bankCode?: string
    bankBranchName?: string
    bankBranchCode?: string
    bankAccountType?: BankAccountType
    bankAccountNumber?: string
    bankAccountHolder?: string
  }

  export type UpdateResponse = {
    bankAccount: BankAccount
  }

  export type Definition = Api.Update<Form, Api.Form<Form>, UpdateResponse>

  export const create = (axios: AxiosInstance): Definition => ({
    async update ({ form }) {
      if (form.userId && form.staffId) {
        throw new Error('Parameter has multiple id')
      } else if (form.userId) {
        return await api.extract(axios.put(api.endpoint('users', form.userId, 'bank-account'), form))
      } else if (form.staffId) {
        return await api.extract(axios.put(api.endpoint('staffs', form.staffId, 'bank-account'), form))
      } else {
        throw new Error('Parameter has no id')
      }
    }
  })
}
