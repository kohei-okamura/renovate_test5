/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Purpose } from '@zinger/enums/lib/purpose'
import { AxiosInstance } from 'axios'
import { DwsAreaGrade } from '~/models/dws-area-grade'
import { HomeHelpServiceCalcSpec } from '~/models/home-help-service-calc-spec'
import { HomeVisitLongTermCareCalcSpec } from '~/models/home-visit-long-term-care-calc-spec'
import { LtcsAreaGrade } from '~/models/ltcs-area-grade'
import { Office, OfficeId } from '~/models/office'
import { OfficeGroup, OfficeGroupId } from '~/models/office-group'
import { VisitingCareForPwsdCalcSpec } from '~/models/visiting-care-for-pwsd-calc-spec'
import { api, Api } from '~/services/api/core'

/**
 * 事業所 API.
 */
export namespace OfficesApi {
  export type Form = {
    purpose?: Purpose
    name?: string
    abbr?: string
    phoneticName?: string
    corporationName?: string
    phoneticCorporationName?: string
    postcode?: string
    prefecture?: Prefecture
    city?: string
    street?: string
    apartment?: string
    tel?: string
    fax?: string
    email?: string
    qualifications?: OfficeQualification[]
    officeGroupId?: OfficeGroupId
    dwsGenericService?: Partial<Office['dwsGenericService']>
    dwsCommAccompanyService?: Partial<Office['dwsCommAccompanyService']>
    ltcsHomeVisitLongTermCareService?: Partial<Office['ltcsHomeVisitLongTermCareService']>
    ltcsCareManagementService?: Partial<Office['ltcsCareManagementService']>
    ltcsCompHomeVisitingService?: Partial<Office['ltcsCompHomeVisitingService']>
    ltcsPreventionService?: Partial<Office['ltcsPreventionService']>
    status?: OfficeStatus
  }

  export type GetParams = Api.GetParams
  export type GetResponse = {
    dwsAreaGrade?: DwsAreaGrade
    homeHelpServiceCalcSpecs?: HomeHelpServiceCalcSpec[]
    homeVisitLongTermCareCalcSpecs?: HomeVisitLongTermCareCalcSpec[]
    ltcsAreaGrade?: LtcsAreaGrade
    office: Office
    officeGroup?: OfficeGroup
    visitingCareForPwsdCalcSpecs?: VisitingCareForPwsdCalcSpec[]
  }

  export type GetIndexParams = Api.GetIndexParams & {
    prefecture?: Prefecture | ''
    q?: string
    status?: OfficeStatus[]
    purpose?: Purpose | ''
  }
  export type GetIndexResponse = Api.GetIndexResponse<Office>

  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form> &
    Api.Get<GetResponse, GetParams> &
    Api.GetIndex<GetIndexParams, GetIndexResponse> &
    Api.Update<Form, Api.UpdateParams<Form>, UpdateResponse>

  const endpoint = (id?: OfficeId) => api.endpoint('offices', id)

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form }) {
      await axios.post(endpoint(), form)
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
