/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { Expense } from '~/models/expense'
import { OfficeId } from '~/models/office'
import { OwnExpenseProgram, OwnExpenseProgramId } from '~/models/own-expense-program'
import { api, Api } from '~/services/api/core'

/**
 * 自費サービス情報 API.
 */
export namespace OwnExpenseProgramsApi {
  export type Form = {
    officeId: OfficeId | undefined
    name: string
    durationMinutes: number
    fee: Expense
    note: string
  }

  export type GetParams = Api.GetParams
  export type GetResponse = {
    ownExpenseProgram: OwnExpenseProgram
  }

  export type GetIndexParams = Api.GetIndexParams & {
    officeId?: number | ''
    q?: string
  }
  export type GetIndexResponse = Api.GetIndexResponse<OwnExpenseProgram>

  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form> &
    Api.Get<GetResponse, GetParams> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<Form, Api.UpdateParams<Form>, UpdateResponse>

  const endpoint = (id?: OwnExpenseProgramId) => api.endpoint('own-expense-programs', id)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form }) {
      await axios.post(endpoint(), form)
    },
    async get ({ id }) {
      return await api.extract(axios.get(endpoint(id)))
    },
    async getIndex (params) {
      return await api.extract(axios.get(endpoint(), { params }))
    },
    async update ({ form, id }) {
      return await api.extract(axios.put(endpoint(id), form))
    }
  })
}
