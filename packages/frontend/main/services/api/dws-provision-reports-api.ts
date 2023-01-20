/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingServiceReportAggregateGroup } from '@zinger/enums/lib/dws-billing-service-report-aggregate-group'
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { DwsProvisionReport } from '~/models/dws-provision-report'
import { DwsProvisionReportDigest } from '~/models/dws-provision-report-digest'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { Job } from '~/models/job'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス予実 API.
 */
export namespace DwsProvisionReportsApi {
  type Identifiers = {
    officeId: OfficeId
    userId: UserId
    providedIn: DateLike
  }

  export type DeleteParams = Identifiers

  export type DownloadForm = Identifiers
  export type DownloadParams = {
    form: DownloadForm
  }
  export type BatchResponse = {
    job: Job
  }
  export type Download = {
    downloadPreviews (params: DownloadParams): Promise<BatchResponse>
  }

  export type GetParams = Identifiers
  export type GetResponse = {
    dwsProvisionReport: DwsProvisionReport
  }

  export type GetIndexParams = Api.GetIndexParams & {
    officeId?: number | ''
    providedIn?: string
    status?: DwsProvisionReportStatus | ''
    q?: string
  }
  export type GetIndexResponse = Api.GetIndexResponse<DwsProvisionReportDigest>

  export type GetTimeSummaryForm = Identifiers & {
    plans: DwsProvisionReportItem[]
    results: DwsProvisionReportItem[]
  }
  export type GetTimeSummaryParams = Api.Form<GetTimeSummaryForm>
  // 通院等乗降介助 は使わない
  export type GetTimeSummaryResponseItem = {
    [K in Exclude<DwsBillingServiceReportAggregateGroup, 15>]?: number
  }
  export type GetTimeSummaryResponse = {
    plan: GetTimeSummaryResponseItem
    result: GetTimeSummaryResponseItem
  }
  export type GetTimeSummary = {
    getTimeSummary (params: GetTimeSummaryParams): Promise<GetTimeSummaryResponse>
  }

  export type UpdateForm = {
    plans: DwsProvisionReportItem[]
    results: DwsProvisionReportItem[]
  }
  export type UpdateParams = Api.Form<UpdateForm> & Identifiers
  export type UpdateResponse = GetResponse

  export type UpdateStatusForm = {
    status: DwsProvisionReportStatus
  }
  export type UpdateStatusParams = Api.Form<UpdateStatusForm> & Identifiers
  export type UpdateStatusResponse = GetResponse
  export type UpdateStatus = {
    updateStatus (params: UpdateStatusParams): Promise<UpdateStatusResponse>
  }

  export type Definition =
    Api.Delete<DeleteParams> &
    Api.Get<GetResponse, GetParams> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<UpdateForm, UpdateParams, UpdateResponse> &
    Download &
    GetTimeSummary &
    UpdateStatus

  const endpoint = (params?: GetParams, last?: string) => {
    const segments = params ? [params.officeId, params.userId, `${params.providedIn}`, last] : []
    return api.endpoint('dws-provision-reports', ...segments)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async delete (params) {
      await axios.delete(endpoint(params))
    },
    async downloadPreviews (params) {
      return await api.extract(axios.post(api.endpoint('dws-service-report-previews'), params.form))
    },
    async get (params) {
      return await api.extract(axios.get(endpoint(params)))
    },
    async getIndex (params) {
      return await api.extract(axios.get(endpoint(), { params }))
    },
    async getTimeSummary (params) {
      return await api.extract(axios.post(api.endpoint('dws-provision-report-time-summary'), params.form))
    },
    async update (params) {
      return await api.extract(axios.put(endpoint(params), params.form))
    },
    async updateStatus (params) {
      return await api.extract(axios.put(endpoint(params, 'status'), params.form))
    }
  })
}
