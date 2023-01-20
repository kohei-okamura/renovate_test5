/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import { StaffStatus } from '@zinger/enums/lib/staff-status'
import { AxiosInstance } from 'axios'
import { BankAccount } from '~/models/bank-account'
import { DateLike } from '~/models/date'
import { InvitationId } from '~/models/invitation'
import { Office } from '~/models/office'
import { Role } from '~/models/role'
import { Staff, StaffId } from '~/models/staff'
import { api, Api } from '~/services/api/core'

/**
 * スタッフ API.
 */
export namespace StaffsApi {
  export type Form = {
    familyName: string
    givenName: string
    phoneticFamilyName: string
    phoneticGivenName: string
    sex: Sex
    birthday: DateLike
    postcode: string
    prefecture: Prefecture
    city: string
    street: string
    apartment: string
    tel: string
    fax: string
    certifications: number[]
    status: StaffStatus
  }

  export type CreateForm = Form & {
    password: string
    invitationId: InvitationId
    token: string
  }

  export type GetParams = Api.GetParams
  export type GetResponse = {
    bankAccount: BankAccount
    offices: Office[]
    roles: Role[]
    staff: Staff
  }

  export type GetIndexParams = Api.GetIndexParams & {
    officeId?: number | ''
    q?: string
    status?: StaffStatus[]
  }
  export type GetIndexResponse = Api.GetIndexResponse<Staff>

  export type UpdateForm = Form & {
    email: string
    employeeNumber?: string
    roleIds?: number[]
    officeIds?: number[]
    officeGroupIds?: number[]
  }
  export type UpdateParams = Api.UpdateParams<UpdateForm>
  export type UpdateResponse = GetResponse

  export type VerifyParams = Api.Token

  export type Verify = {
    verify (params: VerifyParams): Promise<void>
  }

  export type Definition =
    Api.Create<CreateForm> &
    Api.Get<GetResponse> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<UpdateForm, UpdateParams, UpdateResponse> &
    Verify

  const endpoint = (id?: StaffId) => api.endpoint('staffs', id)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form }) {
      await axios.post(endpoint(), form)
    },
    async get ({ id }) {
      return await api.extract(axios.get(endpoint(id)))
    },
    async getIndex (params) {
      return await api.extract(axios.get(endpoint(), { params }))
    },
    async update ({ form, id }) {
      return await api.extract(axios.put(endpoint(id), form))
    },
    async verify ({ token }) {
      await axios.put(api.endpoint('staff-verifications', token), { isVerified: true })
    }
  })
}
