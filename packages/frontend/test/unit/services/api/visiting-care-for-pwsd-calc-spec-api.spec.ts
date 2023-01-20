/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'
import { createVisitingCareForPwsdCalcSpecResponseStub } from '~~/stubs/create-visiting-care-for-pwsd-calc-spec-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/visiting-care-for-pwsd-calc-specs-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let visitingCareForPwsdCalcSpecs: VisitingCareForPwsdCalcSpecsApi.Definition

  beforeEach(() => {
    visitingCareForPwsdCalcSpecs = VisitingCareForPwsdCalcSpecsApi.create(axios)
  })

  const form: VisitingCareForPwsdCalcSpecsApi.Form = {
    period: { start: '1976-01-19', end: '2007-07-06' },
    specifiedOfficeAddition: 3,
    treatmentImprovementAddition: 3,
    specifiedTreatmentImprovementAddition: 2
  }

  describe('create', () => {
    const officeId = 1

    it('should post /api/offices/:officeId/visiting-care-for-pwsd-calc-specs', async () => {
      const url = `/api/offices/${officeId}/visiting-care-for-pwsd-calc-specs`
      adapter.setup(x => {
        x.onPost().replyOnce(HttpStatusCode.Created)
      })

      await visitingCareForPwsdCalcSpecs.create({ form, officeId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/offices/${officeId}/visiting-care-for-pwsd-calc-specs`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = visitingCareForPwsdCalcSpecs.create({ form, officeId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/offices/:officeId/visiting-care-for-pwsd-calc-specs/:id', async () => {
      const id = 1
      const officeId = 1
      const url = `/api/offices/${officeId}/visiting-care-for-pwsd-calc-specs/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createVisitingCareForPwsdCalcSpecResponseStub(id))
      })

      await visitingCareForPwsdCalcSpecs.get({ id, officeId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      const officeId = 2
      adapter.setup(x => {
        x.onGet(`/api/offices/${officeId}/visiting-care-for-pwsd-calc-specs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = visitingCareForPwsdCalcSpecs.get({ id, officeId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const officeId = 1

    it('should put /api/offices/:officeId/visiting-care-for-pwsd-calc-specs/:id', async () => {
      const id = 1
      const url = `/api/offices/${officeId}/visiting-care-for-pwsd-calc-specs/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await visitingCareForPwsdCalcSpecs.update({ form, id, officeId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/offices/${officeId}/visiting-care-for-pwsd-calc-specs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })
      const promise = visitingCareForPwsdCalcSpecs.update({ form, id, officeId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
