/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { RoleScope } from '@zinger/enums/lib/role-scope'
import { AxiosInstance } from 'axios'
import { Role, RoleId } from '~/models/role'
import { api, Api } from '~/services/api/core'

/**
 * ロール API.
 */
export namespace RolesApi {

  export type Form = {
    name?: string
    isSystemAdmin?: boolean
    permissions: Partial<Record<Permission, boolean>>
    scope?: RoleScope
  }

  export type GetParams = Api.GetParams
  export type GetResponse = {
    role: Role
  }

  export type GetIndexParams = Api.GetIndexParams
  export type GetIndexResponse = Api.GetIndexResponse<Role>

  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form> &
    Api.Delete &
    Api.Get<GetResponse, GetParams> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<Form, Api.UpdateParams<Form>, UpdateResponse>

  const endpoint = (id?: RoleId) => api.endpoint('roles', id)

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
    async update ({ id, form }) {
      return await api.extract(axios.put(endpoint(id), form))
    }
  })

}
