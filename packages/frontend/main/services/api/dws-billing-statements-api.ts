/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingStatementCopayCoordinationStatus } from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { AxiosInstance } from 'axios'
import { DwsBilling, DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundle, DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingStatement, DwsBillingStatementId } from '~/models/dws-billing-statement'
import { DwsBillingStatementAggregate } from '~/models/dws-billing-statement-aggregate'
import { Job } from '~/models/job'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス：明細書 API.
 */
export namespace DwsBillingStatementsApi {
  type Identifiers = {
    billingId: DwsBillingId
    bundleId: DwsBillingBundleId
  }

  export type GetParams = Api.GetParams & Identifiers
  export type GetResponse = {
    billing: DwsBilling
    bundle: DwsBillingBundle
    statement: DwsBillingStatement
    serviceCodeDictionary: Record<string, string>
  }

  export type UpdateForm = {
    aggregates: Pick<DwsBillingStatementAggregate, 'serviceDivisionCode' | 'managedCopay' | 'subtotalSubsidy'>[]
  }
  export type UpdateParams = Api.UpdateParams<UpdateForm> & Identifiers
  export type UpdateResponse = GetResponse

  export type UpdateCopayCoordinationForm = {
    result: CopayCoordinationResult
    amount: number
  }
  export type UpdateCopayCoordinationParams = Api.UpdateParams<UpdateCopayCoordinationForm> & Identifiers
  export type UpdateCopayCoordinationResponse = GetResponse
  export type UpdateCopayCoordination = {
    updateCopayCoordination (params: UpdateCopayCoordinationParams): Promise<UpdateCopayCoordinationResponse>
  }

  export type UpdateCopayCoordinationStatusForm = {
    status: DwsBillingStatementCopayCoordinationStatus
  }
  export type UpdateCopayCoordinationStatusParams = Api.UpdateParams<UpdateCopayCoordinationStatusForm> & Identifiers
  export type UpdateCopayCoordinationStatusResponse = GetResponse
  export type UpdateCopayCoordinationStatus = {
    updateCopayCoordinationStatus (
      params: UpdateCopayCoordinationStatusParams
    ): Promise<UpdateCopayCoordinationStatusResponse>
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
    ids: DwsBillingStatementId[]
    status: DwsBillingStatus
  }
  export type BulkUpdateStatusParams = {
    billingId: DwsBillingId
    form: BulkUpdateStatusForm
  }
  export type BulkUpdateStatus = {
    bulkUpdateStatus (params: BulkUpdateStatusParams): Promise<BatchResponse>
  }
  export type RefreshForm = {
    ids: DwsBillingStatementId[]
  }
  export type RefreshParams = {
    billingId: DwsBillingId
    form: RefreshForm
  }
  export type Refresh = {
    refresh (params: RefreshParams): Promise<BatchResponse>
  }

  export type Definition = Api.Get<GetResponse, GetParams>
    & Api.Update<UpdateForm, UpdateParams, UpdateResponse>
    & UpdateCopayCoordination
    & UpdateCopayCoordinationStatus
    & UpdateStatus
    & BulkUpdateStatus
    & Refresh

  const endpoint = ({ billingId, bundleId, id }: GetParams, last?: string) => {
    return api.endpoint('dws-billings', billingId, 'bundles', bundleId, 'statements', id, last)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async bulkUpdateStatus ({ billingId, form }) {
      return await api.extract(
        axios.post(api.endpoint('dws-billings', billingId, 'statement-status-update'), form)
      )
    },
    async get (params) {
      return await api.extract(axios.get(endpoint(params)))
    },
    async refresh ({ billingId, form }) {
      return await api.extract(axios.post(api.endpoint('dws-billings', billingId, 'statement-refresh'), form))
    },
    async update (params) {
      return await api.extract(axios.put(endpoint(params), params.form))
    },
    async updateCopayCoordination (params) {
      return await api.extract(axios.put(endpoint(params, 'copay-coordination'), params.form))
    },
    async updateCopayCoordinationStatus (params) {
      return await api.extract(axios.put(endpoint(params, 'copay-coordination-status'), params.form))
    },
    async updateStatus (params) {
      return await api.extract(axios.put(endpoint(params, 'status'), params.form))
    }
  })
}
