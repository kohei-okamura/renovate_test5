/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsUserLocationAddition } from '@zinger/enums/lib/ltcs-user-location-addition'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { UserLtcsCalcSpec, UserLtcsCalcSpecId } from '~/models/user-ltcs-calc-spec'
import { api, Api } from '~/services/api/core'

/**
 * 介護保険サービス：利用者別算定情報 API.
 */
export namespace UserLtcsCalcSpecsApi {
  export type Form = {
    effectivatedOn: DateLike
    locationAddition: LtcsUserLocationAddition
  }

  type UserId = {
    userId: number
  }

  export type CreateParams = Api.CreateParams<Form> & UserId

  export type GetParams = Api.GetParams & UserId
  export type GetResponse = {
    ltcsCalcSpec: UserLtcsCalcSpec
  }

  export type UpdateParams = Api.UpdateParams<Form> & UserId
  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams, UpdateResponse>

  const endpoint = (userId: UserId['userId'], id?: UserLtcsCalcSpecId) => api.endpoint('users', userId, 'ltcs-calc-specs', id)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form, userId }) {
      await axios.post(endpoint(userId), form)
    },
    async get ({ id, userId }) {
      return await api.extract(axios.get(endpoint(userId, id)))
    },
    async update ({ form, id, userId }) {
      return await api.extract(axios.put(endpoint(userId, id), form))
    }
  })
}
