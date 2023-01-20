/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { DwsProjectServiceMenu } from '~/models/dws-project-service-menu'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス：計画：サービス内容 API.
 */
export namespace DwsProjectServiceMenusApi {
  export type GetIndexParams = Api.GetIndexParams
  export type GetIndexResponse = Api.GetIndexResponse<DwsProjectServiceMenu>

  export type Definition = Api.GetIndex<GetIndexParams, GetIndexResponse>

  export const create = (axios: AxiosInstance): Definition => ({
    async getIndex (params) {
      return await api.extract(axios.get(api.endpoint('dws-project-service-menus'), { params }))
    }
  })
}
