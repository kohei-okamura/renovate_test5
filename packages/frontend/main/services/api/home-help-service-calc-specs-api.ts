/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBaseIncreaseSupportAddition } from '@zinger/enums/lib/dws-base-increase-support-addition'
import { DwsSpecifiedTreatmentImprovementAddition } from '@zinger/enums/lib/dws-specified-treatment-improvement-addition'
import { DwsTreatmentImprovementAddition } from '@zinger/enums/lib/dws-treatment-improvement-addition'
import { HomeHelpServiceSpecifiedOfficeAddition } from '@zinger/enums/lib/home-help-service-specified-office-addition'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { HomeHelpServiceCalcSpec, HomeHelpServiceCalcSpecId } from '~/models/home-help-service-calc-spec'
import { Range } from '~/models/range'
import { api, Api } from '~/services/api/core'

/**
 * 障害福祉サービス：居宅介護：算定情報 API
 */
export namespace HomeHelpServiceCalcSpecsApi {
  export type Form = {
    period?: Partial<Range<DateLike>>
    specifiedOfficeAddition?: HomeHelpServiceSpecifiedOfficeAddition
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
    homeHelpServiceCalcSpec: HomeHelpServiceCalcSpec
  }

  export type UpdateParams = Api.UpdateParams<Form> & OfficeId

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams>

  const endpoint = (officeId: OfficeId['officeId'], id?: HomeHelpServiceCalcSpecId) => {
    return api.endpoint('offices', officeId, 'home-help-service-calc-specs', id)
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
