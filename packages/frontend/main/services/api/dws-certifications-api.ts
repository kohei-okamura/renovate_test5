/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsLevel } from '@zinger/enums/lib/dws-level'
import { DwsType } from '@zinger/enums/lib/dws-type'
import { AxiosInstance } from 'axios'
import { Child } from '~/models/child'
import { CopayCoordination } from '~/models/copay-coordination'
import { DateLike } from '~/models/date'
import { DwsCertification, DwsCertificationId } from '~/models/dws-certification'
import { api, Api } from '~/services/api/core'

/**
 * 利用者：障害福祉サービス受給者証 API.
 */
export namespace DwsCertificationsApi {
  export type Form = {
    child: Child
    effectivatedOn: DateLike
    status: DwsCertificationStatus
    dwsNumber: string
    dwsTypes: DwsType[]
    issuedOn: DateLike
    cityName: string
    cityCode: string
    dwsLevel: DwsLevel
    isSubjectOfComprehensiveSupport: boolean
    activatedOn: DateLike
    deactivatedOn: DateLike
    grants: Array<{
      dwsCertificationServiceType?: DwsCertificationServiceType
      grantedAmount?: string
      activatedOn?: DateLike
      deactivatedOn?: DateLike
    }>
    copayLimit: number
    copayActivatedOn: DateLike
    copayDeactivatedOn: DateLike
    copayCoordination: Partial<CopayCoordination>
    agreements: Array<{
      indexNumber?: number
      officeId?: number
      dwsCertificationAgreementType?: number
      paymentAmount?: number
      agreedOn?: DateLike
      expiredOn?: DateLike
    }>
  }

  type UserId = {
    userId: number
  }

  export type CreateParams = Api.CreateParams<Form> & UserId

  export type DeleteParams = Api.DeleteParams & UserId

  export type GetParams = Api.GetParams & UserId

  export type GetResponse = {
    dwsCertification: DwsCertification
  }

  export type UpdateParams = Api.UpdateParams<Form> & UserId

  export type Definition =
    Api.Create<Form, CreateParams> &
    Api.Delete<DeleteParams> &
    Api.Get<GetResponse, GetParams> &
    Api.Update<Form, UpdateParams>

  const endpoint = (userId: UserId['userId'], id?: DwsCertificationId) => {
    return api.endpoint('users', userId, 'dws-certifications', id)
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
      await axios.put(endpoint(userId, id), form)
    }
  })
}
