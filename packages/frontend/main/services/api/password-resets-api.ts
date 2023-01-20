/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { api, Api } from '~/services/api/core'

/**
 * パスワード再設定 API.
 */
export namespace PasswordResetsApi {
  export type CommitForm = {
    password: string
  }

  export type CreateForm = {
    email: string
  }

  export type CommitParams = Api.Form<CommitForm> & Api.Token

  export type CreateParams = Api.Form<CreateForm>

  export type VerifyParams = Api.Token

  export type Definition = {
    commit (params: CommitParams): Promise<void>
    create (params: CreateParams): Promise<void>
    verify (params: VerifyParams): Promise<void>
  }

  const endpoint = (token?: Api.Token['token']) => api.endpoint('password-resets', token)

  export const create = (axios: AxiosInstance): Definition => ({
    async commit ({ form, token }) {
      await axios.put(endpoint(token), form)
    },
    async create ({ form }) {
      await axios.post(endpoint(), form)
    },
    async verify ({ token }) {
      await axios.get(endpoint(token))
    }
  })
}
