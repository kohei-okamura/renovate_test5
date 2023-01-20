/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { AxiosInstance } from 'axios'
import { Job } from '~/models/job'
import { LtcsBilling, LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundle, LtcsBillingBundleId } from '~/models/ltcs-billing-bundle'
import { LtcsBillingStatement, LtcsBillingStatementId } from '~/models/ltcs-billing-statement'
import { LtcsBillingStatementAggregate } from '~/models/ltcs-billing-statement-aggregate'
import { api, Api } from '~/services/api/core'

/**
 * 介護保険サービス：請求：明細書 API.
 */
export namespace LtcsBillingStatementsApi {
  export type GetParams = Api.GetParams & {
    billingId: number
    bundleId: number
  }
  export type GetResponse = {
    billing: LtcsBilling
    bundle: LtcsBillingBundle
    statement: LtcsBillingStatement
  }

  export type UpdateForm = {
    aggregates: Pick<LtcsBillingStatementAggregate, 'serviceDivisionCode' | 'plannedScore'>[]
  }
  export type UpdateParams = GetParams & {
    form: UpdateForm
  }
  export type UpdateResponse = GetResponse

  export type UpdateStatusForm = {
    status: LtcsBillingStatus
  }
  export type UpdateStatusParams = GetParams & {
    form: UpdateStatusForm
  }
  export type UpdateStatusResponse = GetResponse
  export type UpdateStatus = {
    updateStatus (params: UpdateStatusParams): Promise<UpdateStatusResponse>
  }
  export type BatchResponse = {
    job: Job
  }
  export type BulkUpdateStatusForm = {
    ids: LtcsBillingStatementId[]
    status: LtcsBillingStatus
  }
  export type BulkUpdateStatusParams = {
    billingId: LtcsBillingId
    bundleId: LtcsBillingBundleId
    form: BulkUpdateStatusForm
  }
  export type BulkUpdateStatus = {
    bulkUpdateStatus(params: BulkUpdateStatusParams): Promise<BatchResponse>
  }
  export type RefreshForm = {
    ids: LtcsBillingStatementId[]
  }
  export type RefreshParams = {
    billingId: LtcsBillingId
    form: RefreshForm
  }
  export type Refresh = {
    refresh(params: RefreshParams): Promise<BatchResponse>
  }

  export type Definition =
    Api.Get<GetResponse, GetParams> &
    Api.Update<UpdateForm, UpdateParams, UpdateResponse> &
    UpdateStatus &
    BulkUpdateStatus &
    Refresh

  const endpoint = (
    billingId: LtcsBillingId,
    bundleId: LtcsBillingBundleId,
    id?: LtcsBillingId,
    ...segments: string[]
  ) => {
    return api.endpoint('ltcs-billings', billingId, 'bundles', bundleId, 'statements', id, ...segments)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async bulkUpdateStatus ({ billingId, bundleId, form }) {
      return await api.extract(axios.post(endpoint(billingId, bundleId, undefined, 'bulk-status'), form))
    },
    async get ({ billingId, bundleId, id }) {
      return await api.extract(axios.get(endpoint(billingId, bundleId, id)))
    },
    async refresh ({ billingId, form }) {
      return await api.extract(axios.post(api.endpoint('ltcs-billings', billingId, 'statement-refresh'), form))
    },
    async update ({ billingId, bundleId, id, form }) {
      return await api.extract(axios.put(endpoint(billingId, bundleId, id), form))
    },
    async updateStatus ({ billingId, bundleId, id, form }) {
      return await api.extract(axios.put(endpoint(billingId, bundleId, id, 'status'), form))
    }
  })
}
