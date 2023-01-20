/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBaseIncreaseSupportAddition } from '@zinger/enums/lib/dws-base-increase-support-addition'
import { DwsSpecifiedTreatmentImprovementAddition } from '@zinger/enums/lib/dws-specified-treatment-improvement-addition'
import { DwsTreatmentImprovementAddition } from '@zinger/enums/lib/dws-treatment-improvement-addition'
import { VisitingCareForPwsdSpecifiedOfficeAddition } from '@zinger/enums/lib/visiting-care-for-pwsd-specified-office-addition'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { Range } from '~/models/range'
import { VisitingCareForPwsdCalcSpec, VisitingCareForPwsdCalcSpecId } from '~/models/visiting-care-for-pwsd-calc-spec'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス：重度訪問介護：算定情報 API
 */
export namespace VisitingCareForPwsdCalcSpecsApi {
  export type Form = {
    period?: Partial<Range<DateLike>>
    specifiedOfficeAddition?: VisitingCareForPwsdSpecifiedOfficeAddition
    treatmentImprovementAddition?: DwsTreatmentImprovementAddition
    specifiedTreatmentImprovementAddition?: DwsSpecifiedTreatmentImprovementAddition
    baseIncreaseSupportAddition?: DwsBaseIncreaseSupportAddition
  }

  type OfficeId = {
    officeId: number
  }

  export type CreateParams = Api.CreateParams<Form> & OfficeId

  export type GetParams = Api.GetParams & OfficeId
  export type GetResponse = {
    visitingCareForPwsdCalcSpec: VisitingCareForPwsdCalcSpec
  }

  export type UpdateParams = Api.UpdateParams<Form> & OfficeId

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams>

  const endpoint = (officeId: OfficeId['officeId'], id?: VisitingCareForPwsdCalcSpecId) => {
    return api.endpoint('offices', officeId, 'visiting-care-for-pwsd-calc-specs', id)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form, officeId }) {
      await axios.post(endpoint(officeId), form)
    },
    async get ({ id, officeId }) {
      return await api.extract(axios.get(endpoint(officeId, id)))
    },
    async update ({ form, id, officeId }) {
      await axios.put(endpoint(officeId, id), form)
    }
  })
}
