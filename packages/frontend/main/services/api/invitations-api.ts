/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { Invitation } from '~/models/invitation'
import { OfficeId } from '~/models/office'
import { OfficeGroupId } from '~/models/office-group'
import { RoleId } from '~/models/role'
import { api, Api } from '~/services/api/core'

/**
 * 招待 API.
 */
export namespace InvitationsApi {
  export type Form = {
    emails: string[]
    officeIds: OfficeId[]
    officeGroupIds: OfficeGroupId[]
    roleIds: RoleId[]
  }

  export type GetParams = {
    token: Invitation['token']
  }
  export type GetResponse = {
    invitation: Invitation
  }

  export type Definition =
    & Api.Create<Form>
    & Api.Get<GetResponse, GetParams>

  const endpoint = (token?: string) => api.endpoint('invitations', token)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form }) {
      await axios.post(endpoint(), form)
    },
    async get ({ token }) {
      return await api.extract(axios.get(endpoint(token)))
    }
  })
}
