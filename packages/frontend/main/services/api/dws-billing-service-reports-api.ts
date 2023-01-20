/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { AxiosInstance } from 'axios'
import { DwsBilling, DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundle, DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingServiceReport, DwsBillingServiceReportId } from '~/models/dws-billing-service-report'
import { Job } from '~/models/job'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス：請求：サービス提供実績記録票 API.
 */
export namespace DwsBillingServiceReportsApi {
  export type Form = {
    status: DwsBillingStatus
  }

  type Identifiers = {
    billingId: DwsBillingId
    bundleId: DwsBillingBundleId
  }

  export type GetParams = Api.GetParams & Identifiers
  export type GetResponse = {
    billing: DwsBilling
    bundle: DwsBillingBundle
    report: DwsBillingServiceReport
  }

  export type UpdateStatusForm = {
    status: DwsBillingStatus
  }
  export type UpdateStatusParams = Api.UpdateParams<UpdateStatusForm> & Identifiers
  export type UpdateStatusResponse = GetResponse
  export type UpdateStatus = {
    updateStatus (params: UpdateStatusParams): Promise<UpdateStatusResponse>
  }
  export type BatchResponse = {
    job: Job
  }
  export type BulkUpdateStatusForm = {
    ids: DwsBillingServiceReportId[]
    status: DwsBillingStatus
  }
  export type BulkUpdateStatusParams = {
    billingId: DwsBillingId
    form: BulkUpdateStatusForm
  }
  export type BulkUpdateStatus = {
    bulkUpdateStatus(params: BulkUpdateStatusParams): Promise<BatchResponse>
  }

  export type Definition = Api.Get<GetResponse, GetParams>
    & UpdateStatus
    & BulkUpdateStatus

  const endpoint = ({ billingId, bundleId, id }: GetParams, last?: string) => {
    return api.endpoint('dws-billings', billingId, 'bundles', bundleId, 'reports', id, last)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async bulkUpdateStatus ({ billingId, form }) {
      return await api.extract(
        axios.post(api.endpoint('dws-billings', billingId, 'service-report-status-update'), form)
      )
    },
    async get (params) {
      return await api.extract(axios.get(endpoint(params)))
    },
    async updateStatus (params) {
      return await api.extract(axios.put(endpoint(params, 'status'), params.form))
    }
  })
}
