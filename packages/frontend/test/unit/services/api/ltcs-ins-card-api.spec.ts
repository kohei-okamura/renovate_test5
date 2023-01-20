/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'
import { createLtcsInsCardResponseStub } from '~~/stubs/create-ltcs-ins-card-response-stub'
import { createLtcsInsCardStub } from '~~/stubs/create-ltcs-ins-card-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/ltcs-ins-cards-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ltcsInsCards: LtcsInsCardsApi.Definition

  beforeEach(() => {
    ltcsInsCards = LtcsInsCardsApi.create(axios)
  })

  const form: LtcsInsCardsApi.Form = {
    effectivatedOn: '2020/01/20',
    status: LtcsInsCardStatus.applied,
    insNumber: '2304316218',
    issuedOn: '2020/01/21',
    insurerNumber: '24009439',
    insurerName: '邑楽郡明和町',
    ltcsLevel: LtcsLevel.careLevel2,
    certificatedOn: '2020/01/22',
    activatedOn: '2020/01/23',
    deactivatedOn: '2020/01/24',
    maxBenefitQuotas: [
      {
        ltcsInsCardServiceType: LtcsInsCardServiceType.serviceType2,
        maxBenefitQuota: 280600
      }
    ],
    carePlanAuthorOfficeId: 2,
    copayRate: 3,
    copayActivatedOn: '2020/01/25',
    copayDeactivatedOn: '2020/01/26'
  }

  describe('create', () => {
    const userId = 0

    it('should post /api/users/:userId/ltcs-ins-cards', async () => {
      const url = `/api/users/${userId}/ltcs-ins-cards`
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await ltcsInsCards.create({ form, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/users/${userId}/ltcs-ins-cards`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsInsCards.create({ form, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('delete', () => {
    const userId = 2
    const id = 1

    it('should delete /api/users/:userId/ltcs-ins-cards/:id', async () => {
      const url = `/api/users/${userId}/ltcs-ins-cards/${id}`
      adapter.setup(x => {
        x.onDelete(url).replyOnce(HttpStatusCode.NoContent)
      })

      await ltcsInsCards.delete({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'delete', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onDelete(`/api/users/${userId}/ltcs-ins-cards/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsInsCards.delete({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/users/:userId/ltcs-ins-cards/:id', async () => {
      const id = 1
      const userId = 10
      const url = `/api/users/${userId}/ltcs-ins-cards/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createLtcsInsCardResponseStub(id))
      })

      await ltcsInsCards.get({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const stub = createLtcsInsCardStub()
      const id = stub.id
      const userId = stub.userId
      const expected = createLtcsInsCardResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/ltcs-ins-cards/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ltcsInsCards.get({ id, userId })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      const userId = 12
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/ltcs-ins-cards/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsInsCards.get({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const userId = 1

    it('should put /api/users/:userId/ltcs-ins-cards/:id', async () => {
      const id = 1
      const url = `/api/users/${userId}/ltcs-ins-cards/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await ltcsInsCards.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/ltcs-ins-cards/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsInsCards.update({ form, id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
