/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { PermissionGroup } from '~/models/permission-group'
import { api, Api } from '~/services/api/core'

/**
 * 権限 API.
 */
export namespace PermissionsApi {
  export type GetIndexParams = Api.GetIndexParams
  export type GetIndexResponse = Api.GetIndexResponse<PermissionGroup>

  export type Definition = Api.GetIndex<GetIndexParams, GetIndexResponse>

  export const create = (axios: AxiosInstance): Definition => ({
    async getIndex (params) {
      return await api.extract(axios.get(api.endpoint('permissions'), { params }))
    }
  })
}
