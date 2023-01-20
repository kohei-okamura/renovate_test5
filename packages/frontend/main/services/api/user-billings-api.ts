/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { UserBillingUsedService } from '@zinger/enums/lib/user-billing-used-service'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { Job } from '~/models/job'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'
import { UserBilling, UserBillingId } from '~/models/user-billing'
import { UserBillingBankAccount } from '~/models/user-billing-bank-account'
import { api, Api } from '~/services/api/core'

/**
 * 利用者請求 API.
 */
export namespace UserBillingsApi {
  export type GetResponse = {
    userBilling: UserBilling
  }
  export type GetParams = Api.GetParams

  export type GetIndexParams = Api.GetIndexParams & {
    providedIn?: string
    issuedIn?: string
    isTransacted?: boolean
    isDeposited?: boolean
    result?: UserBillingResult
    paymentMethod?: PaymentMethod
    usedService?: UserBillingUsedService
    userId?: UserId
    officeId?: OfficeId
  }
  export type GetIndexResponse = Api.GetIndexResponse<UserBilling>

  export type UpdateForm = {
    bankAccount: UserBillingBankAccount
    carriedOverAmount: number
    paymentMethod: PaymentMethod
  }
  export type UpdateParams = Api.UpdateParams<UpdateForm>
  export type UpdateRespose = GetResponse

  export type BatchResponse = {
    job: Job
  }

  export type DepositCancellationForm = {
    ids: UserBillingId[]
  }
  export type DepositCancellationParams = {
    form: DepositCancellationForm
  }
  export type DepositCancellation = {
    depositCancellation (params: DepositCancellationParams): Promise<BatchResponse>
  }

  export type DepositRegistrationForm = {
    ids: UserBillingId[]
    depositedOn: DateLike
  }
  export type DepositRegistrationParams = {
    form: DepositRegistrationForm
  }
  export type DepositRegistration = {
    depositRegistration (params: DepositRegistrationParams): Promise<BatchResponse>
  }

  export type DownloadForm = {
    ids: UserBillingId[]
    issuedOn: DateLike
  }
  export type DownloadParams = {
    form: DownloadForm
  }
  export type Download = {
    downloadInvoices (params: DownloadParams): Promise<BatchResponse>
    downloadNotices (params: DownloadParams): Promise<BatchResponse>
    downloadReceipts (params: DownloadParams): Promise<BatchResponse>
    downloadStatements (params: DownloadParams): Promise<BatchResponse>
  }

  export type Definition =
    Api.Get<GetResponse, GetParams> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<UpdateForm, UpdateParams, UpdateRespose> &
    DepositCancellation &
    DepositRegistration &
    Download

  const endpoint = (...segments: (string | UserBillingId)[]) => api.endpoint('user-billings', ...segments)

  export const create = (axios: AxiosInstance): Definition => ({
    async depositCancellation ({ form }) {
      return await api.extract(axios.post(endpoint('deposit-cancellation'), form))
    },
    async depositRegistration ({ form }) {
      return await api.extract(axios.post(endpoint('deposit-registration'), form))
    },
    // 請求書
    async downloadInvoices ({ form }) {
      return await api.extract(axios.post(api.endpoint('user-billing-invoices'), form))
    },
    // 代理受領額通知書
    async downloadNotices ({ form }) {
      return await api.extract(axios.post(api.endpoint('user-billing-notices'), form))
    },
    // 領収書
    async downloadReceipts ({ form }) {
      return await api.extract(axios.post(api.endpoint('user-billing-receipts'), form))
    },
    // 介護サービス利用明細書
    async downloadStatements ({ form }) {
      return await api.extract(axios.post(api.endpoint('user-billing-statements'), form))
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
