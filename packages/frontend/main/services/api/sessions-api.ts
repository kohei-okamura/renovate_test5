/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { Auth } from '~/models/auth'
import { api, Api } from '~/services/api/core'

/**
 * セッション API.
 */
export namespace SessionsApi {
  export type Form = {
    email: string
    password: string
    rememberMe: boolean
  }

  export type CreateParams = Api.CreateParams<Form>
  export type CreateResponse = {
    auth: Auth
  }

  export type GetResponse = {
    auth: Auth
  }

  export type Definition = {
    create (params: CreateParams): Promise<CreateResponse>
    delete (): Promise<void>
    get (): Promise<GetResponse>
  }

  const endpoint = (...segments: string[]) => api.endpoint('sessions', ...segments)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form }) {
      return await api.extract(axios.post(endpoint(), form))
    },
    async delete () {
      await axios.delete(endpoint())
    },
    async get () {
      return await api.extract(axios.get(endpoint('my')))
    }
  })
}
