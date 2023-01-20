/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { LtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { AxiosInstance } from 'axios'
import { Contract, ContractId, ContractPeriod } from '~/models/contract'
import { DateLike } from '~/models/date'
import { User } from '~/models/user'
import { api, Api } from '~/services/api/core'

/**
 * 利用者：介護保険サービス契約 API.
 */
export namespace LtcsContractsApi {
  type UserId = {
    userId: User['id']
  }

  type BaseForm = {
    officeId?: undefined
    status?: undefined
    contractedOn?: undefined
    terminatedOn?: undefined
    dwsPeriods?: undefined
    ltcsPeriod?: undefined
    expiredReason?: undefined
    note?: undefined
  }

  export type CreateForm = Overwrite<BaseForm, {
    officeId: number
    note: string
  }>
  export type CreateParams = Api.CreateParams<CreateForm> & UserId

  export type GetParams = Api.GetParams & UserId
  export type GetResponse = {
    contract: Contract
  }

  export type UpdateForm =
    Overwrite<BaseForm, {
      officeId: number
      status: ContractStatus
      contractedOn: DateLike
      terminatedOn: DateLike | undefined
      ltcsPeriod: ContractPeriod
      expiredReason: LtcsExpiredReason
      note: string
    }> |
    Overwrite<BaseForm, {
      status: typeof ContractStatus.disabled
    }>
  export type UpdateParams = Api.UpdateParams<UpdateForm> & UserId
  export type UpdateResponse = GetResponse

  export type DisableParams = GetParams
  export type DisableResponse = UpdateResponse
  export type Disable = {
    disable (params: DisableParams): Promise<DisableResponse>
  }

  export type Definition =
    Api.Create<CreateForm, CreateParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<UpdateForm, UpdateParams, UpdateResponse> &
    Disable

  const endpoint = (userId: UserId['userId'], id?: ContractId) => {
    return api.endpoint('users', userId, 'ltcs-contracts', id)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form, userId }) {
      await axios.post(endpoint(userId), form)
    },
    async disable ({ id, userId }) {
      return await api.extract(axios.put(endpoint(userId, id), {
        status: ContractStatus.disabled
      }))
    },
    async get ({ id, userId }) {
      return await api.extract(axios.get(endpoint(userId, id)))
    },
    async update ({ form, id, userId }) {
      return await api.extract(axios.put(endpoint(userId, id), form))
    }
  })
}
