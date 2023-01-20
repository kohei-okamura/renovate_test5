/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { Postcode } from '~/models/postcode'
import { api } from '~/services/api/core'
import { isAxiosError } from '~/support'

/**
 * 郵便番号検索 API.
 */
export namespace PostcodeApi {
  export type GetParams = {
    postcode: string
  }

  export type GetResponse = Postcode[]

  export type Definition = {
    get (params: GetParams): Promise<GetResponse>
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async get (params) {
      const m = params.postcode.match(/^(\d{3})-?(\d{4})$/) ?? undefined
      if (m === undefined) {
        return []
      }
      const url = `${process.env.postcodeResolverURL}${m[1]}/${m[1]}${m[2]}.json`
      const headers = {
        'Content-Type': 'application/json;charset=UTF-8',
        'Access-Control-Allow-Origin': '*'
      }
      try {
        return await api.extract(axios.get<GetResponse>(url, { headers }))
      } catch (reason) {
        if (
          isAxiosError(reason) &&
          reason.response &&
          [HttpStatusCode.Forbidden, HttpStatusCode.NotFound].includes(reason.response.status)
        ) {
          return []
        } else {
          throw reason
        }
      }
    }
  })
}
