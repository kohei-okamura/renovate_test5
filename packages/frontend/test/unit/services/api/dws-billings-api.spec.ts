/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { JobStatus } from '@zinger/enums/lib/job-status'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { createDwsBillingIndexResponseStub } from '~~/stubs/create-dws-billing-index-response-stub'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/dws-billings-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsBillings: DwsBillingsApi.Definition

  beforeEach(() => {
    dwsBillings = DwsBillingsApi.create(axios)
  })

  describe('create', () => {
    const form: DwsBillingsApi.CreateForm = {
      officeId: OFFICE_ID_MIN,
      transactedIn: '2020-10',
      providedIn: '2020-10'
    }

    it('should post /api/dws-billings', async () => {
      const url = '/api/dws-billings'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await dwsBillings.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/dws-billings').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsBillings.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('file', () => {
    const token = 'x'.repeat(60)

    it('should get /api/dws-billings/:id/files/:token', async () => {
      const id = 1
      const url = `/api/dws-billings/${id}/files/${token}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, { url: 'https://www.example.com/file.pdf' })
      })

      await dwsBillings.file({ id, token })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      const expected = {
        url: 'https://www.example.com/file.pdf'
      }
      adapter.setup(x => {
        x.onGet(`/api/dws-billings/${id}/files/${token}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await dwsBillings.file({ id, token })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/dws-billings/${id}/files/${token}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillings.file({ id, token })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    const id = 1
    const responseStub = createDwsBillingResponseStub(id, 1)

    it('should get /api/dws-billings/:id', async () => {
      const url = `/api/dws-billings/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, responseStub)
      })

      await dwsBillings.get({ id })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const response = responseStub
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onGet(`/api/dws-billings/${id}`).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillings.get({ id })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/dws-billings/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillings.get({ id })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const responseStub = createDwsBillingIndexResponseStub(undefined, undefined, 1)
    const params: DwsBillingsApi.GetIndexParams = {
      all: true
    }

    it('should get /api/dws-billings', async () => {
      const url = '/api/dws-billings'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, responseStub)
      })

      await dwsBillings.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = responseStub
      adapter.setup(x => {
        x.onGet('/api/dws-billings').replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await dwsBillings.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/dws-billings').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsBillings.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('updateStatus', () => {
    const id = 10
    const responseStub = createDwsBillingResponseStub(id, 1)
    const form: DwsBillingsApi.UpdateStatusForm = {
      status: DwsBillingStatus.fixed
    }

    it('should put /api/dws-billings/:id/status', async () => {
      const url = `/api/dws-billings/${id}/status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, responseStub)
      })

      await dwsBillings.updateStatus({ id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const response = responseStub
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(`/api/dws-billings/${id}/status`).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillings.updateStatus({ id, form })

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 11
      adapter.setup(x => {
        x.onPut(`/api/dws-billings/${id}/status`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillings.updateStatus({ id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('copy', () => {
    const id = 1
    const url = `/api/dws-billings/${id}/copy`

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Accepted)
      })

      await dwsBillings.copy({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
    })

    it('should return response of the api', async () => {
      const response = createJobResponseStub('token', JobStatus.waiting)
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPost(`/api/dws-billings/${id}/copy`).replyOnce(HttpStatusCode.Accepted, response)
      })

      const actual = await dwsBillings.copy({ id })

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsBillings.copy({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
