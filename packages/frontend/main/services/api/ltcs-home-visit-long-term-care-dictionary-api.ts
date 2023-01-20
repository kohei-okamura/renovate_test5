/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { AxiosInstance } from 'axios'
import { DateLike, DateString } from '~/models/date'
import { LtcsHomeVisitLongTermCareDictionaryEntry } from '~/models/ltcs-home-visit-long-term-care-dictionary-entry'
import { OfficeId } from '~/models/office'
import { api, Api } from '~/services/api/core'

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ API.
 */
export namespace LtcsHomeVisitLongTermCareDictionaryApi {
  export type GetParams = {
    serviceCode: string
    providedIn: DateLike
  }
  export type GetResponse = {
    dictionaryEntry: LtcsHomeVisitLongTermCareDictionaryEntry
  }

  export type GetIndexParams = {
    officeId: OfficeId
    isEffectiveOn: DateString
    q?: string
    timeframe?: Timeframe
    category?: LtcsProjectServiceCategory
    physicalMinutes?: number
    houseworkMinutes?: number
    headcount?: number
  }

  export type GetIndexResponse = Api.GetIndexResponse<LtcsHomeVisitLongTermCareDictionaryEntry>

  export type Definition =
    Api.Get<GetResponse, GetParams> &
    Api.GetIndex<GetIndexParams, GetIndexResponse>

  export const create = (axios: AxiosInstance): Definition => ({
    async getIndex (params) {
      return await api.extract(axios.get(api.endpoint('ltcs-home-visit-long-term-care-dictionary'), { params }))
    },
    async get (params) {
      const { serviceCode, ...queryParams } = params
      return await api.extract(axios.get(api.endpoint('ltcs-home-visit-long-term-care-dictionary-entries', serviceCode), { params: queryParams }))
    }
  })
}
