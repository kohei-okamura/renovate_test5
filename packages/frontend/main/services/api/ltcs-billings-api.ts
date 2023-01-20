/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { AxiosInstance } from 'axios'
import { Job } from '~/models/job'
import { LtcsBilling, LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundle } from '~/models/ltcs-billing-bundle'
import { LtcsBillingStatement } from '~/models/ltcs-billing-statement'
import { api, Api } from '~/services/api/core'

/**
 * 介護保険サービス：請求 API.
 */
export namespace LtcsBillingsApi {
  export type CreateForm = {
    officeId?: number
    transactedIn?: string
    providedIn?: string
  }
  export type CreateParams = {
    form: CreateForm
  }
  export type CreateResponse = {
    job: Job
  }
  export type Create = {
    create (params: CreateParams): Promise<CreateResponse>
  }

  export type FileParams = Api.Id & Api.Token
  export type FileResponse = Api.Url
  export type File = {
    file (params: FileParams): Promise<FileResponse>
  }

  export type GetParams = Api.GetParams
  export type GetResponse = {
    billing: LtcsBilling
    bundles: LtcsBillingBundle[]
    statements: LtcsBillingStatement[]
  }

  export type GetIndexParams = Api.GetIndexParams & {
    officeId?: number | ''
    statuses?: LtcsBillingStatus[] | ''
    start?: string
    end?: string
  }
  export type GetIndexResponse = Api.GetIndexResponse<LtcsBilling>

  export type UpdateStatusForm = {
    status: LtcsBillingStatus
  }
  export type UpdateStatusParams = Api.Id & {
    form: UpdateStatusForm
  }
  export type UpdateStatusResponse = GetResponse & {
    job?: Job
  }
  export type UpdateStatus = {
    updateStatus (params: UpdateStatusParams): Promise<UpdateStatusResponse>
  }

  export type Definition = Create &
    Api.Get<GetResponse> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    File &
    UpdateStatus

  const endpoint = (id?: LtcsBillingId, ...segments: string[]) => api.endpoint('ltcs-billings', id, ...segments)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form }) {
      return await api.extract(axios.post(endpoint(), form))
    },
    async file ({ id, token }) {
      return await api.extract(axios.get(endpoint(id, 'files', token)))
    },
    async get ({ id }) {
      return await api.extract(axios.get(endpoint(id)))
    },
    async getIndex (params) {
      return await api.extract(axios.get(endpoint(), { params }))
    },
    async updateStatus ({ id, form }) {
      return await api.extract(axios.put(endpoint(id, 'status'), form))
    }
  })
}
