/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { LtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { AxiosInstance } from 'axios'
import { DateLike } from '~/models/date'
import { LtcsInsCard, LtcsInsCardId } from '~/models/ltcs-ins-card'
import { OfficeId } from '~/models/office'
import { api, Api } from '~/services/api/core'

/**
 * 介護保険被保険者証 API.
 */
export namespace LtcsInsCardsApi {
  export type Form = {
    effectivatedOn?: DateLike
    status?: LtcsInsCardStatus
    insNumber?: string
    issuedOn?: DateLike
    insurerNumber?: string
    insurerName?: string
    ltcsLevel?: LtcsLevel
    certificatedOn?: DateLike
    activatedOn?: DateLike
    deactivatedOn?: DateLike
    maxBenefitQuotas: Array<{
      ltcsInsCardServiceType?: LtcsInsCardServiceType
      maxBenefitQuota?: number
    }>
    careManagerName?: string
    carePlanAuthorType?: LtcsCarePlanAuthorType
    communityGeneralSupportCenterId?: OfficeId
    carePlanAuthorOfficeId?: OfficeId
    copayRate?: number
    copayActivatedOn?: DateLike
    copayDeactivatedOn?: DateLike
  }

  type UserId = {
    userId: number
  }

  export type CreateParams = Api.CreateParams<Form> & UserId

  export type DeleteParams = Api.DeleteParams & UserId

  export type GetParams = Api.GetParams & UserId
  export type GetResponse = {
    ltcsInsCard: LtcsInsCard
  }

  export type UpdateParams = Api.UpdateParams<Form> & UserId
  export type UpdateResponse = GetResponse

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Delete<DeleteParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams, UpdateResponse>

  const endpoint = (userId: UserId['userId'], id?: LtcsInsCardId) => {
    return api.endpoint('users', userId, 'ltcs-ins-cards', id)
  }

  export const create = (axios: AxiosInstance): Definition => ({
    async create ({ form, userId }) {
      await axios.post(endpoint(userId), form)
    },
    async delete ({ id, userId }) {
      await axios.delete(endpoint(userId, id))
    },
    async get ({ id, userId }) {
      return await api.extract(axios.get(endpoint(userId, id)))
    },
    async update ({ form, id, userId }) {
      return await api.extract(axios.put(endpoint(userId, id), form))
    }
  })
}
