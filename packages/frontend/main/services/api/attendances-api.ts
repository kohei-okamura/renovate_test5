/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Task } from '@zinger/enums/lib/task'
import { AxiosInstance } from 'axios'
import { Assignee } from '~/models/assignee'
import { Attendance, AttendanceId } from '~/models/attendance'
import { ContractId } from '~/models/contract'
import { Duration } from '~/models/duration'
import { Job } from '~/models/job'
import { OfficeId } from '~/models/office'
import { Schedule } from '~/models/schedule'
import { StaffId } from '~/models/staff'
import { UserId } from '~/models/user'
import { api, Api } from '~/services/api/core'

/**
 * 勤務実績 API.
 */
export namespace AttendancesApi {
  export type Form = {
    task: Task
    serviceCode: string
    officeId: OfficeId | undefined
    userId: UserId | undefined
    contractId: ContractId | undefined
    assignerId: StaffId
    assignees: Assignee[]
    headcount: number
    schedule: Schedule
    durations: Duration[]
    options: ServiceOption[]
    note: string
  }

  export type GetParams = Api.GetParams
  export type GetResponse = {
    attendance: Attendance
  }

  export type GetIndexParams = Api.GetIndexParams & {
    start?: string
    end?: string
    officeId?: number | ''
    userId?: number | ''
    assigneeId?: number | ''
    assignerId?: number | ''
    isConfirmed?: boolean | ''
    task?: Task | ''
  }
  export type GetIndexResponse = Api.GetIndexResponse<Attendance>

  export type UpdateResponse = GetResponse

  export type BatchResponse = {
    job: Job
  }

  export type BatchCancel = {
    batchCancel (params: { ids: AttendanceId[], reason: string }): Promise<BatchResponse>
  }

  export type Cancel = {
    cancel (params: { id: AttendanceId, reason: string }): Promise<void>
  }

  export type Confirm = {
    confirm (params: { ids: AttendanceId[] }): Promise<BatchResponse>
  }

  export type Definition =
    BatchCancel &
    Cancel &
    Confirm &
    Api.Create<Form> &
    Api.Get<GetResponse> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<Form, Api.UpdateParams<Form>, UpdateResponse>

  const endpoint = (...segments: (string | AttendanceId)[]) => api.endpoint('attendances', ...segments)

  export const create = (axios: AxiosInstance): Definition => ({
    async batchCancel (params) {
      return await api.extract(axios.post(endpoint('cancel'), { params }))
    },
    async cancel ({ id, reason }) {
      await axios.post(endpoint(id, 'cancel'), { reason })
    },
    async confirm (params) {
      return await api.extract(axios.post(endpoint('confirmation'), params))
    },
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
