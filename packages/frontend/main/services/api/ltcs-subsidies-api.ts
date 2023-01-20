/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DefrayerCategory } from '@zinger/enums/lib/defrayer-category'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { Range } from '~/models/range'
import { UserLtcsSubsidy, UserLtcsSubsidyId } from '~/models/user-ltcs-subsidy'
import { api, Api } from '~/services/api/core'

/**
 * 公費情報 API.
 */
export namespace LtcsSubsidiesApi {
  export type Form = {
    period: Partial<Range<DateLike>>
    defrayerCategory: DefrayerCategory
    defrayerNumber: string
    recipientNumber: string
    benefitRate: number
    copay: number
  }

  type UserId = {
    userId: number
  }

  export type CreateParams = Api.CreateParams<Form> & UserId

  export type DeleteParams = Api.DeleteParams & UserId

  export type GetParams = Api.GetParams & UserId
  export type GetResponse = {
    ltcsSubsidy: UserLtcsSubsidy
  }

  export type UpdateParams = Api.UpdateParams<Form> & UserId

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Delete<DeleteParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams>

  const endpoint = (userId: UserId['userId'], id?: UserLtcsSubsidyId) => {
    return api.endpoint('users', userId, 'ltcs-subsidies', id)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form, userId }) {
      await axios.post(endpoint(userId), form)
    },
    async delete ({ id, userId }) {
      await axios.delete(endpoint(userId, id))
    },
    async get ({ id, userId }) {
      return await api.extract(axios.get(endpoint(userId, id)))
    },
    async update ({ form, id, userId }) {
      await axios.put(endpoint(userId, id), form)
    }
  })
}
