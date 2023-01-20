/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { AxiosInstance } from 'axios'
import { DwsBilling, DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundle } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordination } from '~/models/dws-billing-copay-coordination'
import { DwsBillingServiceReport } from '~/models/dws-billing-service-report'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { Job } from '~/models/job'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス：請求 API.
 */
export namespace DwsBillingsApi {

  export type CopyParams = {
    id: DwsBillingId
  }

  export type CopyResponse = {
    job: Job
  }

  export type Copy = {
    copy (params: CopyParams): Promise<CopyResponse>
  }

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
    billing: DwsBilling
    bundles: DwsBillingBundle[]
    statements: DwsBillingStatement[]
    reports: DwsBillingServiceReport[]
    copayCoordinations: DwsBillingCopayCoordination[]
  }

  export type GetIndexParams = Api.GetIndexParams & {
    officeId?: number | ''
    statuses?: DwsBillingStatus[] | ''
    start?: string
    end?: string
  }
  export type GetIndexResponse = Api.GetIndexResponse<DwsBilling>

  export type UpdateStatusForm = {
    status: DwsBillingStatus
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

  export type Definition =
    Copy &
    Create &
    Api.Get<GetResponse> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    File &
    UpdateStatus

  const endpoint = (id?: DwsBillingId, ...segments: string[]) => api.endpoint('dws-billings', id, ...segments)

  export const create = (axios: AxiosInstance): Definition => ({
    async copy ({ id }) {
      return await api.extract(axios.post(api.endpoint('dws-billings', id, 'copy')))
    },
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
