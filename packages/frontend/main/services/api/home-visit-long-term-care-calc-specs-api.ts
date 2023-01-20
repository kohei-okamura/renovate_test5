/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsSpecifiedTreatmentImprovementAddition } from '@zinger/enums/lib/dws-specified-treatment-improvement-addition'
import { DwsTreatmentImprovementAddition } from '@zinger/enums/lib/dws-treatment-improvement-addition'
import { HomeVisitLongTermCareSpecifiedOfficeAddition } from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { LtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import {
  HomeVisitLongTermCareCalcSpec,
  HomeVisitLongTermCareCalcSpecId
} from '~/models/home-visit-long-term-care-calc-spec'
import { Range } from '~/models/range'
import { api, Api } from '~/services/api/core'

/**
 * 介護保険サービス：訪問介護：算定情報 API
 */
export namespace HomeVisitLongTermCareCalcSpecsApi {
  export type Form = {
    period?: Partial<Range<DateLike>>
    locationAddition?: LtcsOfficeLocationAddition
    specifiedOfficeAddition?: HomeVisitLongTermCareSpecifiedOfficeAddition
    treatmentImprovementAddition?: DwsTreatmentImprovementAddition
    specifiedTreatmentImprovementAddition?: DwsSpecifiedTreatmentImprovementAddition
    baseIncreaseSupportAddition?: LtcsBaseIncreaseSupportAddition
  }

  type OfficeId = {
    officeId: number
  }

  export type GetParams = Api.GetParams & OfficeId
  export type GetResponse = {
    homeVisitLongTermCareCalcSpec: HomeVisitLongTermCareCalcSpec
  }

  export type CreateParams = Api.CreateParams<Form> & OfficeId
  export type CreateResponse = GetResponse & { provisionReportCount: number }

  export type GetOneParams = OfficeId & {
    providedIn: DateLike
    passthroughErrors?: true
  }
  export type GetOneResponse = {
    homeVisitLongTermCareCalcSpec: HomeVisitLongTermCareCalcSpec
  }
  export type GetOne = {
    getOne (params: GetOneParams): Promise<GetOneResponse>
  }

  export type UpdateParams = Api.UpdateParams<Form> & OfficeId
  export type UpdateResponse = GetResponse & { provisionReportCount: number }

  export type Definition =
    Api.Create<Form, CreateParams, CreateResponse> &
    Api.Get<GetResponse, GetParams> &
    GetOne &
    Api.Update<Form, UpdateParams, UpdateResponse>

  const endpoint = (officeId: OfficeId['officeId'], id?: HomeVisitLongTermCareCalcSpecId) => {
    return api.endpoint('offices', officeId, 'home-visit-long-term-care-calc-specs', id)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form, officeId }) {
      return await api.extract(axios.post(endpoint(officeId), form))
    },
    async get ({ id, officeId }) {
      return await api.extract(axios.get(endpoint(officeId, id)))
    },
    async getOne ({ officeId, providedIn, passthroughErrors }) {
      return await api.extract(axios.get(endpoint(officeId), { params: { providedIn }, passthroughErrors }))
    },
    async update ({ form, id, officeId }) {
      return await api.extract(axios.put(endpoint(officeId, id), form))
    }
  })
}
