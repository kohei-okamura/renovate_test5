/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { OfficeGroup, OfficeGroupId } from '~/models/office-group'
import { api, Api } from '~/services/api/core'

/**
 * 事業所グループ API.
 */
export namespace OfficeGroupsApi {
  export type Form = {
    name: string
    parentOfficeGroupId?: number
    sortOrder?: number
  }

  export type GetIndexParams = {
    all?: true
  }
  export type GetIndexResponse = Api.GetIndexResponse<OfficeGroup>

  export type GetResponse = {
    officeGroup: OfficeGroup
  }

  export type UpdateResponse = GetIndexResponse

  export type SortParams = {
    list: OfficeGroup[]
  }
  export type SortResponse = {
    list: OfficeGroup[]
  }
  export type Sort = {
    sort (params: SortParams): Promise<SortResponse>
  }

  export type Definition =
    Api.Create<Form> &
    Api.Delete &
    Api.Get<GetResponse> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<Form, Api.UpdateParams<Form>, UpdateResponse> &
    Sort

  const endpoint = (id?: OfficeGroupId) => api.endpoint('office-groups', id)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form }) {
      await axios.post(endpoint(), form)
    },
    async delete ({ id }) {
      await axios.delete(endpoint(id))
    },
    async get ({ id }) {
      return await api.extract(axios.get(endpoint(id)))
    },
    async getIndex (params) {
      return await api.extract(axios.get(endpoint(), { params }))
    },
    async sort (params) {
      return await api.extract(axios.put(endpoint(), params))
    },
    async update ({ id, form }) {
      return await api.extract(axios.put(endpoint(id), form))
    }
  })
}
