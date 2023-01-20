/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { Range } from '~/models/range'
import { UserDwsSubsidy, UserDwsSubsidyId } from '~/models/user-dws-subsidy'
import { api, Api } from '~/services/api/core'

/**
 * 自治体助成情報 API.
 */
export namespace DwsSubsidiesApi {
  export type Form = {
    period: Partial<Range<DateLike>>
    cityName: string
    cityCode: string
    subsidyType: UserDwsSubsidyType
    factor?: UserDwsSubsidyFactor
    benefitRate?: number
    copayRate?: number
    rounding?: Rounding
    benefitAmount?: number
    copayAmount?: number
    note?: string
  }

  type UserId = {
    userId: number
  }

  export type CreateParams = Api.CreateParams<Form> & UserId

  export type GetParams = Api.GetParams & UserId
  export type GetResponse = {
    dwsSubsidy: UserDwsSubsidy
  }

  export type UpdateParams = Api.UpdateParams<Form> & UserId
  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams, UpdateResponse>

  const endpoint = (userId: UserId['userId'], id?: UserDwsSubsidyId) => {
    return api.endpoint('users', userId, 'dws-subsidies', id)
  }

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
