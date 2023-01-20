/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { DwsProject, DwsProjectId } from '~/models/dws-project'
import { DwsProjectProgram } from '~/models/dws-project-program'
import { OfficeId } from '~/models/office'
import { StaffId } from '~/models/staff'
import { api, Api } from '~/services/api/core'

/**
 * 利用者：障害福祉サービス計画 API.
 */
export namespace DwsProjectsApi {
  export type Form = {
    officeId: OfficeId
    staffId: StaffId
    writtenOn: DateLike
    effectivatedOn: DateLike
    requestFromUser: string
    requestFromFamily: string
    objective: string
    programs: Writable<DwsProjectProgram>[]
  }

  type UserId = {
    userId: number
  }

  export type CreateParams = Api.CreateParams<Form> & UserId

  export type GetParams = Api.GetParams & UserId
  export type GetResponse = {
    dwsProject: DwsProject
  }

  export type UpdateParams = Api.UpdateParams<Form> & UserId
  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams, UpdateResponse>

  const endpoint = (userId: UserId['userId'], id?: DwsProjectId) => api.endpoint('users', userId, 'dws-projects', id)

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
