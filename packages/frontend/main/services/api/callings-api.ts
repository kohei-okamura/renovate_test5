/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { Shift } from '~/models/shift'
import { api, Api } from '~/services/api/core'

/**
 * スタッフ：出勤確認 API.
 */
export namespace CallingsApi {
  export type AcknowledgeParams = Api.Token
  export type Acknowledge = {
    acknowledge (token: string): Promise<void>
  }

  export type GetIndex = {
    getIndex (token: string): Promise<Api.GetIndexResponse<Shift>>
  }

  export type Definition = Acknowledge & GetIndex

  const endpoint = (...segments: string[]) => api.endpoint('callings', ...segments)

  export const create = (axios: AxiosInstance): Definition => ({
    async acknowledge (token) {
      await axios.post(endpoint(token, 'acknowledges'))
    },
    async getIndex (token) {
      return await api.extract(axios.get(endpoint(token, 'shifts')))
    }
  })
}
