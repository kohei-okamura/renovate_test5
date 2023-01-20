/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { LtcsAreaGrade } from '~/models/ltcs-area-grade'
import { api, Api } from '~/services/api/core'

/**
 * 介保地域区分 API.
 */
export namespace LtcsAreaGradesApi {
  export type GetIndexParams = {
    all?: true
  }

  export type GetIndexResponse = Api.GetIndexResponse<LtcsAreaGrade>

  export type Definition = Api.GetIndex<GetIndexParams, GetIndexResponse>

  export const create = (axios: AxiosInstance): Definition => ({
    async getIndex (params) {
      return await api.extract(axios.get(api.endpoint('ltcs-area-grades'), { params }))
    }
  })
}
