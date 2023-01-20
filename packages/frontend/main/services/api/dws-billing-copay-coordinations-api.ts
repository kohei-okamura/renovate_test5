/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingCopayCoordinationExchangeAim } from '@zinger/enums/lib/dws-billing-copay-coordination-exchange-aim'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { AxiosInstance } from 'axios'
import { DwsBilling, DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundle, DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordination } from '~/models/dws-billing-copay-coordination'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス：請求：利用者負担上限額管理結果票 API.
 */
export namespace DwsBillingCopayCoordinationsApi {
  export type Subtotal = {
    fee: number
    copay: number
    coordinatedCopay: number
  }
  export type Item = {
    officeId: OfficeId
    subtotal: Subtotal
  }
  export type Form = {
    userId: UserId
    items: Item[]
    exchangeAim: DwsBillingCopayCoordinationExchangeAim
    result: CopayCoordinationResult
    isProvided: boolean
  }
  export type DataSet = {
    billing: DwsBilling
    bundle: DwsBillingBundle
    copayCoordination: DwsBillingCopayCoordination
  }

  type Identifiers = {
    billingId: DwsBillingId
    bundleId: DwsBillingBundleId
  }

  export type CreateParams = Api.CreateParams<Form> & Identifiers
  export type CreateResponse = DataSet

  export type GetParams = Api.GetParams & Identifiers
  export type GetResponse = DataSet

  export type UpdateParams = Api.UpdateParams<Form> & Identifiers
  export type UpdateResponse = DataSet

  export type UpdateStatusForm = {
    status: DwsBillingStatus
  }
  export type UpdateStatusParams = Api.UpdateParams<UpdateStatusForm> & Identifiers
  export type UpdateStatusResponse = GetResponse
  export type UpdateStatus = {
    updateStatus (params: UpdateStatusParams): Promise<UpdateStatusResponse>
  }

  export type Definition = Api.Create<Form, CreateParams, CreateResponse> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams, UpdateResponse> &
    UpdateStatus

  const endpoint = ({ billingId, bundleId, id }: Partial<GetParams>, last?: string) => {
    return api.endpoint('dws-billings', billingId, 'bundles', bundleId, 'copay-coordinations', id, last)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async create (params) {
      return await api.extract(axios.post(endpoint(params), params.form))
    },
    async get (params) {
      return await api.extract(axios.get(endpoint(params)))
    },
    async update (params) {
      return await api.extract(axios.put(endpoint(params), params.form))
    },
    async updateStatus (params) {
      return await api.extract(axios.put(endpoint(params, 'status'), params.form))
    }
  })
}
