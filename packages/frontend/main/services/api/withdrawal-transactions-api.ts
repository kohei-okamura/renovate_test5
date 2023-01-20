/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { Job } from '~/models/job'
import { UserBillingId } from '~/models/user-billing'
import { WithdrawalTransaction, WithdrawalTransactionId } from '~/models/withdrawal-transaction'
import { api, Api } from '~/services/api/core'

/**
 * 全銀ファイル（口座振替データ） API.
 */
export namespace WithdrawalTransactionsApi {
  export type Form = {
    //
  }
  export type GetIndexParams = Api.GetIndexParams & {
    start?: string
    end?: string
  }
  export type GetIndexResponse = Api.GetIndexResponse<WithdrawalTransaction>

  export type BatchResponse = {
    job: Job
  }
  export type DownloadForm = {
    id: WithdrawalTransactionId
  }
  export type DownloadParams = {
    form: DownloadForm
  }
  export type Download = {
    download (params: DownloadParams): Promise<BatchResponse>
  }
  export type CreateForm = {
    userBillingIds: UserBillingId[]
  }
  export type CreateParams = {
    form: CreateForm
  }
  export type Create = {
    create (params: CreateParams): Promise<BatchResponse>
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
    Create &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Download &
    Import

  const endpoint = (...segments: (string | WithdrawalTransactionId)[]) => api.endpoint('withdrawal-transactions', ...segments)

  export const create = (axios: AxiosInstance): Definition => ({
    // 口座振替データ作成
    async create ({ form }) {
      return await api.extract(axios.post(endpoint(), form))
    },
    async import ({ form }) {
      const headers = {
        'Content-Type': 'multipart/form-data'
      }
      const data = new FormData()
      data.append('file', form.file ?? '')
      return await api.extract(axios.post(api.endpoint('withdrawal-transaction-imports'), data, { headers }))
    },
    async download ({ form }) {
      return await api.extract(axios.post(api.endpoint('withdrawal-transaction-files'), form))
    },
    async getIndex (params) {
      return await api.extract(axios.get(endpoint(), { params }))
    }
  })
}
