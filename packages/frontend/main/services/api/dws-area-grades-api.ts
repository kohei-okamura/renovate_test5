/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { DwsAreaGrade } from '~/models/dws-area-grade'
import { api, Api } from '~/services/api/core'

/**
 * 障害地域区分 API.
 */
export namespace DwsAreaGradesApi {
  export type GetIndexParams = {
    all: true
  }

  export type GetIndexResponse = Api.GetIndexResponse<DwsAreaGrade>

  export type Definition = Api.GetIndex<GetIndexParams, GetIndexResponse>

  export const create = (axios: AxiosInstance): Definition => ({
    async getIndex (params) {
      return await api.extract(axios.get(api.endpoint('dws-area-grades'), { params }))
    }
  })
}
