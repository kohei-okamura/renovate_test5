/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'
import { createHomeHelpServiceCalcSpecResponseStub } from '~~/stubs/create-home-help-service-calc-spec-response-stub'
import { createHomeVisitLongTermCareCalcSpecStub } from '~~/stubs/create-home-visit-long-term-care-calc-spec-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/home-visit-long-term-care-calc-specs-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let homeVisitLongTermCareCalcSpecs: HomeVisitLongTermCareCalcSpecsApi.Definition

  beforeEach(() => {
    homeVisitLongTermCareCalcSpecs = HomeVisitLongTermCareCalcSpecsApi.create(axios)
  })

  const form: HomeVisitLongTermCareCalcSpecsApi.Form = {
    period: { start: '1976-01-19', end: '2007-07-06' },
    locationAddition: 2,
    specifiedOfficeAddition: 3,
    treatmentImprovementAddition: 3,
    specifiedTreatmentImprovementAddition: 2
  }

  describe('create', () => {
    const officeId = 1

    it('should post /api/offices/:officeId/home-visit-long-term-care-calc-specs', async () => {
      const url = `/api/offices/${officeId}/home-visit-long-term-care-calc-specs`
      adapter.setup(x => {
        x.onPost().replyOnce(HttpStatusCode.Created)
      })

      await homeVisitLongTermCareCalcSpecs.create({ form, officeId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/offices/${officeId}/home-visit-long-term-care-calc-specs`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = homeVisitLongTermCareCalcSpecs.create({ form, officeId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/offices/:officeId/home-visit-long-term-care-calc-specs/:id', async () => {
      const id = 1
      const officeId = 1
      const url = `/api/offices/${officeId}/home-visit-long-term-care-calc-specs/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createHomeVisitLongTermCareCalcSpecStub(id))
      })

      await homeVisitLongTermCareCalcSpecs.get({ id, officeId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      const officeId = 2
      adapter.setup(x => {
        x.onGet(`/api/offices/${officeId}/home-visit-long-term-care-calc-specs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = homeVisitLongTermCareCalcSpecs.get({ id, officeId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getOne', () => {
    it('should get /api/offices/:officeId/home-visit-long-term-care-calc-specs', async () => {
      const officeId = 1
      const providedIn = '2022-02'
      const url = `/api/offices/${officeId}/home-visit-long-term-care-calc-specs`
      const params = { providedIn }
      adapter.setup(x => {
        x.onGet(url, { params })
          .replyOnce(HttpStatusCode.OK, createHomeHelpServiceCalcSpecResponseStub())
      })

      await homeVisitLongTermCareCalcSpecs.getOne({ officeId, providedIn })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url, params })
    })

    it('should throw an AxiosError when failed to request', async () => {
      const officeId = 2
      const providedIn = '2022-02'
      adapter.setup(x => {
        x.onGet(`/api/offices/${officeId}/home-visit-long-term-care-calc-specs`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = homeVisitLongTermCareCalcSpecs.getOne({ officeId, providedIn })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const officeId = 1

    it('should put /api/offices/:officeId/home-visit-long-term-care-calc-specs/:id', async () => {
      const id = 1
      const url = `/api/offices/${officeId}/home-visit-long-term-care-calc-specs/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await homeVisitLongTermCareCalcSpecs.update({ form, id, officeId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/offices/${officeId}/home-visit-long-term-care-calc-specs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })
      const promise = homeVisitLongTermCareCalcSpecs.update({ form, id, officeId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
