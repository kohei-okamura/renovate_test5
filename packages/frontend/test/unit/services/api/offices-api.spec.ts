/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { OfficesApi } from '~/services/api/offices-api'
import { createOfficeIndexResponseStub } from '~~/stubs/create-office-index-response-stub'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/offices-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let offices: OfficesApi.Definition

  beforeEach(() => {
    offices = OfficesApi.create(axios)
  })

  describe('create', () => {
    const form: OfficesApi.Form = {}

    it('should post /api/offices', async () => {
      const url = '/api/offices'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await offices.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/offices').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = offices.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/offices/:id', async () => {
      const id = 1
      const url = `/api/offices/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.Created, createOfficeStub())
      })

      await offices.get({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      adapter.setup(x => {
        x.onGet(`/api/offices/${id}`).replyOnce(HttpStatusCode.Created, createOfficeResponseStub(id))
      })

      const response = await offices.get({ id })

      expect(response).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/offices/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = offices.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const params: OfficesApi.GetIndexParams = {
      all: true
    }

    it('should get /api/offices', async () => {
      const url = '/api/offices'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createOfficeIndexResponseStub(params))
      })

      await offices.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onGet('/api/offices').replyOnce(HttpStatusCode.OK, createOfficeIndexResponseStub(params))
      })

      const result = await offices.getIndex(params)

      expect(result).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/offices').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = offices.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: OfficesApi.Form = {}

    it('should put /api/offices/:id', async () => {
      const id = 1
      const url = `/api/offices/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await offices.update({ id, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 2
      const office = createOfficeStub(id)
      const expected = { office }
      const form = {
        purpose: office.purpose,
        name: office.name,
        abbr: office.abbr,
        phoneticName: office.phoneticName,
        corporationName: '',
        phoneticCorporationName: '',
        tel: office.tel,
        fax: office.fax,
        email: office.email,
        qualifications: [],
        officeGroupId: office.officeGroupId,
        dwsGenericService: office.dwsGenericService,
        dwsCommAccompanyService: office.dwsCommAccompanyService,
        ltcsHomeVisitLongTermCareService: office.ltcsHomeVisitLongTermCareService,
        ltcsCareManagementService: office.ltcsCareManagementService,
        ltcsCompHomeVisitingService: office.ltcsCompHomeVisitingService,
        ltcsPreventionService: office.ltcsPreventionService,
        status: OfficeStatus.inOperation,
        ...office.addr
      }

      adapter.setup(x => {
        x.onPut(`/api/offices/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await offices.update({ id, form })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/offices/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = offices.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
