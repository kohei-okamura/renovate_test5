/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  HomeVisitLongTermCareSpecifiedOfficeAddition
} from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { LtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { LtcsProvisionReportStatus } from '@zinger/enums/lib/ltcs-provision-report-status'
import {
  LtcsSpecifiedTreatmentImprovementAddition
} from '@zinger/enums/lib/ltcs-specified-treatment-improvement-addition'
import { LtcsTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-treatment-improvement-addition'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { Job } from '~/models/job'
import { LtcsProvisionReport } from '~/models/ltcs-provision-report'
import { LtcsProvisionReportDigest } from '~/models/ltcs-provision-report-digest'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { LtcsProvisionReportOverScore } from '~/models/ltcs-provision-report-over-score'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'
import { api, Api } from '~/services/api/core'

/**
 * 介護保険サービス予実 API.
 */
export namespace LtcsProvisionReportsApi {
  type Identifiers = {
    officeId: OfficeId
    userId: UserId
    providedIn: DateLike
  }

  export type DeleteParams = Identifiers

  export type BatchResponse = {
    job: Job
  }

  export type DownloadForm = {
    officeId: OfficeId
    userId: UserId
    providedIn: DateLike
    issuedOn: DateLike
    needsMaskingInsNumber?: boolean | ''
    needsMaskingInsName?: boolean | ''
  }
  export type DownloadParams = {
    form: DownloadForm
  }
  export type Download = {
    downloadSheets (params: DownloadParams): Promise<BatchResponse>
  }

  export type GetParams = Identifiers
  export type GetResponse = {
    ltcsProvisionReport: LtcsProvisionReport
  }

  export type GetIndexParams = Api.GetIndexParams & {
    officeId?: number | ''
    providedIn?: string
    status?: LtcsProvisionReportStatus | ''
    q?: string
  }
  export type GetIndexResponse = Api.GetIndexResponse<LtcsProvisionReportDigest>

  export type GetScoreSummaryForm = Identifiers & {
    entries: LtcsProvisionReportEntry[]
    specifiedOfficeAddition: HomeVisitLongTermCareSpecifiedOfficeAddition
    treatmentImprovementAddition: LtcsTreatmentImprovementAddition
    specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition
    baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition
    locationAddition: LtcsOfficeLocationAddition
    plan: LtcsProvisionReportOverScore
    result: LtcsProvisionReportOverScore
  }
  export type GetScoreSummaryParams = Api.Form<GetScoreSummaryForm>
  export type GetScoreSummaryResponse = {
    plan: {
      managedScore: number
      unmanagedScore: number
    }
    result: {
      managedScore: number
      unmanagedScore: number
    }
  }
  export type GetScoreSummary = {
    getScoreSummary (params: GetScoreSummaryParams): Promise<GetScoreSummaryResponse>
  }

  export type UpdateForm = {
    entries: LtcsProvisionReportEntry[]
    specifiedOfficeAddition: HomeVisitLongTermCareSpecifiedOfficeAddition
    treatmentImprovementAddition: LtcsTreatmentImprovementAddition
    specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition
    baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition
    locationAddition: LtcsOfficeLocationAddition
    plan: LtcsProvisionReportOverScore
    result: LtcsProvisionReportOverScore
  }
  export type UpdateParams = Api.Form<UpdateForm> & Identifiers
  export type UpdateResponse = GetResponse

  export type UpdateStatusForm = {
    status: LtcsProvisionReportStatus
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
    GetScoreSummary &
    UpdateStatus

  const endpoint = (params?: GetParams, last?: string) => {
    const segments = params ? [params.officeId, params.userId, `${params.providedIn}`, last] : []
    return api.endpoint('ltcs-provision-reports', ...segments)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async delete (params) {
      await axios.delete(endpoint(params))
    },
    // サービス提供票
    async downloadSheets ({ form }) {
      return await api.extract(axios.post(api.endpoint('ltcs-provision-report-sheets'), form))
    },
    async get (params) {
      return await api.extract(axios.get(endpoint(params)))
    },
    async getIndex (params) {
      return await api.extract(axios.get(endpoint(), { params }))
    },
    async getScoreSummary (params) {
      return await api.extract(axios.post(api.endpoint('ltcs-provision-report-score-summary'), params.form))
    },
    async update (params) {
      return await api.extract(axios.put(endpoint(params), params.form))
    },
    async updateStatus (params) {
      return await api.extract(axios.put(endpoint(params, 'status'), params.form))
    }
  })
}
