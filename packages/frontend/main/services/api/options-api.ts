/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { Permission } from '@zinger/enums/lib/permission'
import { Purpose } from '@zinger/enums/lib/purpose'
import { AxiosInstance } from 'axios'
import { OfficeId } from '~/models/office'
import { OfficeGroupId } from '~/models/office-group'
import { RoleId } from '~/models/role'
import { StaffId } from '~/models/staff'
import { UserId } from '~/models/user'
import { VSelectOption } from '~/models/vuetify'
import { api } from '~/services/api/core'

/**
 * 選択肢 API.
 */
export namespace OptionsApi {
  type Params = {
    permission: Permission
  }

  export type OfficesParams = Partial<Params> & {
    isCommunityGeneralSupportCenter?: boolean
    purpose?: Exclude<Purpose, typeof Purpose.unknown>
    qualifications?: OfficeQualification[]
    userId?: number
  }
  export type OfficesResponse = {
    keyword: string
    text: string
    value: OfficeId
  }[]
  export type Offices = {
    offices (params: OfficesParams): Promise<OfficesResponse>
  }

  export type OfficeGroupsParams = Params
  export type OfficeGroupsResponse = VSelectOption<OfficeGroupId>[]
  export type OfficeGroups = {
    officeGroups (params: OfficeGroupsParams): Promise<OfficeGroupsResponse>
  }

  export type RolesParams = Params
  export type RolesResponse = VSelectOption<RoleId>[]
  export type Roles = {
    roles (params: RolesParams): Promise<RolesResponse>
  }

  export type StaffsParams = Params & {
    officeIds?: number[]
  }
  export type StaffsResponse = VSelectOption<StaffId>[]
  export type Staffs = {
    staffs (params: StaffsParams): Promise<StaffsResponse>
  }

  export type UsersParams = Params & {
    officeIds?: number[]
  }
  export type UsersResponse = VSelectOption<UserId>[]
  export type Users = {
    users (params: UsersParams): Promise<UsersResponse>
  }

  export type Definition =
    Offices &
    OfficeGroups &
    Roles &
    Staffs &
    Users

  const endpoint = (name: string) => api.endpoint('options', name)

  export const create = (axios: AxiosInstance): Definition => ({
    async offices (params) {
      return await api.extract(axios.get(endpoint('offices'), { params }))
    },
    async officeGroups (params) {
      return await api.extract(axios.get(endpoint('office-groups'), { params }))
    },
    async roles (params) {
      return await api.extract(axios.get(endpoint('roles'), { params }))
    },
    async staffs (params) {
      return await api.extract(axios.get(endpoint('staffs'), { params }))
    },
    async users (params) {
      return await api.extract(axios.get(endpoint('users'), { params }))
    }
  })
}
