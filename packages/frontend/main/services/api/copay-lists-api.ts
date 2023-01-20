/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { DwsBillingId } from '~/models/dws-billing'
import { DwsBillingStatementId } from '~/models/dws-billing-statement'
import { Job } from '~/models/job'
import { api } from '~/services/api/core'

/**
 * 利用者負担額一覧表 API.
 */
export namespace CopayListsApi {

  export type BatchResponse = {
    job: Job
  }

  export type DownloadForm = {
    ids: DwsBillingStatementId[]
    isDivided: boolean
  }

  export type DownloadParams = {
    billingId: DwsBillingId
    form: DownloadForm
  }

  export type Download = {
    download (params: DownloadParams): Promise<BatchResponse>
  }

  export type Definition = Download

  export const create = (axios: AxiosInstance): Definition => ({
    async download ({ billingId, form }) {
      return await api.extract(axios.post(api.endpoint('dws-billings', billingId, 'copay-lists'), form))
    }
  })
}
