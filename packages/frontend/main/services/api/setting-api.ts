/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { Setting } from '~/models/setting'
import { Api, api } from '~/services/api/core'

/**
 * 事業者別設定 API.
 */
export namespace SettingApi {
  export type Form = {
    bankingClientCode: string
  }
  export type GetResponse = {
    organizationSetting: Setting
  }
  export type UpdateResponse = {
    organizationSetting: Setting
  }

  export type Definition = {
    get (): Promise<GetResponse>
    update (form: Api.Form<Form>): Promise<UpdateResponse>
  } & Api.Create<Form>

  const endpoint = api.endpoint('setting')

  export const create = (axios: AxiosInstance): Definition => ({
    async get () {
      return await api.extract(axios.get(endpoint))
    },
    async create ({ form }) {
      await axios.post(endpoint, form)
    },
    async update ({ form }) {
      return await api.extract(axios.put(endpoint, form))
    }
  })
}
