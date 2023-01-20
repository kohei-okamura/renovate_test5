/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { ContractId } from '~/models/contract'
import { DateLike } from '~/models/date'
import { LtcsProject, LtcsProjectId } from '~/models/ltcs-project'
import { LtcsProjectProgram } from '~/models/ltcs-project-program'
import { Objective } from '~/models/objective'
import { OfficeId } from '~/models/office'
import { StaffId } from '~/models/staff'
import { api, Api } from '~/services/api/core'

/**
 * 利用者：介護保険サービス計画 API.
 */
export namespace LtcsProjectsApi {
  export type Form = {
    contractId: ContractId
    officeId: OfficeId
    staffId: StaffId
    writtenOn: DateLike
    effectivatedOn: DateLike
    requestFromUser: string
    requestFromFamily: string
    problem: string
    programs: DeepPartial<LtcsProjectProgram>[]
    longTermObjective: DeepPartial<Objective>
    shortTermObjective: DeepPartial<Objective>
  }

  type UserId = {
    userId: number
  }

  export type CreateParams = Api.CreateParams<Form> & UserId

  export type GetParams = Api.GetParams & UserId
  export type GetResponse = {
    ltcsProject: LtcsProject
  }

  export type UpdateParams = Api.UpdateParams<Form> & UserId
  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams, UpdateResponse>

  const endpoint = (userId: UserId['userId'], id?: LtcsProjectId) => api.endpoint('users', userId, 'ltcs-projects', id)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form, userId }) {
      await axios.post(endpoint(userId), form)
    },
    async get ({ id, userId }) {
      return await api.extract(axios.get(endpoint(userId, id)))
    },
    async update ({ form, id, userId }) {
      return await api.extract(axios.put(endpoint(userId, id), form))
    }
  })
}
