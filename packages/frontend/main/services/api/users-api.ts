/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import { AxiosInstance } from 'axios'
import { BankAccount } from '~/models/bank-account'
import { Contact } from '~/models/contact'
import { Contract } from '~/models/contract'
import { DateLike } from '~/models/date'
import { DwsCertification } from '~/models/dws-certification'
import { DwsProject } from '~/models/dws-project'
import { LtcsInsCard } from '~/models/ltcs-ins-card'
import { LtcsProject } from '~/models/ltcs-project'
import { User, UserId } from '~/models/user'
import { UserBillingDestination } from '~/models/user-billing-destination'
import { UserDwsCalcSpec } from '~/models/user-dws-calc-spec'
import { UserDwsSubsidy } from '~/models/user-dws-subsidy'
import { UserLtcsCalcSpec } from '~/models/user-ltcs-calc-spec'
import { UserLtcsSubsidy } from '~/models/user-ltcs-subsidy'
import { api, Api } from '~/services/api/core'

/**
 * 利用者 API.
 */
export namespace UsersApi {
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
    contacts: Partial<Writable<Contact>>[]
    isEnabled: boolean
    billingDestination: UserBillingDestination
  }

  export type GetResponse = {
    bankAccount: BankAccount
    contracts: Contract[]
    dwsCertifications: DwsCertification[]
    dwsProjects: DwsProject[]
    dwsSubsidies: UserDwsSubsidy[]
    dwsCalcSpecs: UserDwsCalcSpec[]
    ltcsInsCards: LtcsInsCard[]
    ltcsProjects: LtcsProject[]
    ltcsSubsidies: UserLtcsSubsidy[]
    ltcsCalcSpecs: UserLtcsCalcSpec[]
    user: User
  }
  export type GetParams = Api.GetParams

  export type GetIndexParams = Api.GetIndexParams & {
    isEnabled?: boolean | ''
    officeId?: number | ''
    q?: string
  }
  export type GetIndexResponse = Api.GetIndexResponse<User>

  export type UpdateParams = Api.UpdateParams<Form>
  export type UpdateResponse = {
    user: User
  }

  export type Definition =
    Api.Create<Form> &
    Api.Get<GetResponse, GetParams> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<Form, UpdateParams, UpdateResponse>

  const endpoint = (id?: UserId) => api.endpoint('users', id)

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
    }
  })
}
