/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Task } from '@zinger/enums/lib/task'
import { AxiosInstance } from 'axios'
import { Assignee } from '~/models/assignee'
import { AttendanceId } from '~/models/attendance'
import { ContractId } from '~/models/contract'
import { DateString } from '~/models/date'
import { Duration } from '~/models/duration'
import { Job } from '~/models/job'
import { OfficeId } from '~/models/office'
import { Range } from '~/models/range'
import { Schedule } from '~/models/schedule'
import { Shift, ShiftId } from '~/models/shift'
import { StaffId } from '~/models/staff'
import { UserId } from '~/models/user'
import { api, Api } from '~/services/api/core'

/**
 * 勤務シフト API.
 */
export namespace ShiftsApi {
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
  export type GetIndexResponse = Api.GetIndexResponse<Shift>

  export type GetParams = Api.GetParams
  export type GetResponse = {
    shift: Shift
  }

  export type UpdateResponse = GetResponse

  export type BatchResponse = {
    job: Job
  }
  export type BatchCancel = {
    batchCancel (params: { ids: ShiftId[], reason: string }): Promise<BatchResponse>
  }
  export type Cancel = {
    cancel (params: { id: ShiftId, reason: string }): Promise<void>
  }
  export type Confirm = {
    confirm (params: { ids: ShiftId[] }): Promise<BatchResponse>
  }

  export type CreateTemplateForm = {
    officeId: OfficeId
    range: Range<DateString>
    isCopy: boolean
    source?: Range<DateString>
  }
  export type CreateTemplateParams = {
    form: CreateTemplateForm
  }
  export type CreateTemplateResponse = {
    job: Job
  }
  export type CreateTemplate = {
    createTemplate (params: CreateTemplateParams): Promise<CreateTemplateResponse>
  }

  export type ImportForm = {
    file: File | undefined
  }
  export type ImportParams = {
    form: ImportForm
  }
  export type Import = {
    import (params: ImportParams): Promise<BatchResponse>
  }

  export type Definition =
    Api.Create<Form> &
    Api.Get<GetResponse> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<Form, Api.UpdateParams<Form>, UpdateResponse> &
    BatchCancel &
    Cancel &
    Confirm &
    CreateTemplate &
    Import

  const endpoint = (...segments: (string | AttendanceId)[]) => api.endpoint('shifts', ...segments)

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
    async createTemplate ({ form }) {
      return await api.extract(axios.post(api.endpoint('shift-templates'), form))
    },
    async get ({ id }) {
      return await api.extract(axios.get(endpoint(id)))
    },
    async getIndex (params) {
      return await api.extract(axios.get(endpoint(), { params }))
    },
    async import ({ form }) {
      const headers = {
        'Content-Type': 'multipart/form-data'
      }
      const data = new FormData()
      data.append('file', form.file ?? '')
      return await api.extract(axios.post(api.endpoint('shift-imports'), data, { headers }))
    },
    async update ({ form, id }) {
      return await api.extract(axios.put(endpoint(id), form))
    }
  })
}
