/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { Job } from '~/models/job'
import { api, Api } from '~/services/api/core'

/**
 * 非同期ジョブ API.
 */
export namespace JobsApi {
  export type GetParams = {
    token: Job['token']
  }
  export type GetResponse = {
    job: Job
  }

  export type Definition = Api.Get<GetResponse, GetParams>

  const endpoint = (token?: GetParams['token']) => api.endpoint('jobs', token)

  export const create = (axios: AxiosInstance): Definition => ({
    async get ({ token }) {
      return await api.extract(axios.get(endpoint(token)))
    }
  })
}
