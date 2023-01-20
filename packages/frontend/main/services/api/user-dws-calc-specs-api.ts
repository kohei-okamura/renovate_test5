/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsUserLocationAddition } from '@zinger/enums/lib/dws-user-location-addition'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { UserDwsCalcSpec, UserDwsCalcSpecId } from '~/models/user-dws-calc-spec'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス：利用者別算定情報 API.
 */
export namespace UserDwsCalcSpecsApi {
  export type Form = {
    effectivatedOn: DateLike
    locationAddition: DwsUserLocationAddition
  }

  type UserId = {
    userId: number
  }

  export type CreateParams = Api.CreateParams<Form> & UserId

  export type GetParams = Api.GetParams & UserId
  export type GetResponse = {
    dwsCalcSpec: UserDwsCalcSpec
  }

  export type UpdateParams = Api.UpdateParams<Form> & UserId
  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams, UpdateResponse>

  const endpoint = (userId: UserId['userId'], id?: UserDwsCalcSpecId) => api.endpoint('users', userId, 'dws-calc-specs', id)

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
