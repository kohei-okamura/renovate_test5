/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { createLtcsBillingIndexResponseStub } from '~~/stubs/create-ltcs-billing-index-response-stub'
import { createLtcsBillingResponseStub } from '~~/stubs/create-ltcs-billing-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/ltcs-billings-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ltcsBillings: LtcsBillingsApi.Definition

  beforeEach(() => {
    ltcsBillings = LtcsBillingsApi.create(axios)
  })

  describe('create', () => {
    const form: LtcsBillingsApi.CreateForm = {
      officeId: 123,
      transactedIn: '2021-03-01',
      providedIn: '2021-02-01'
    }

    it('should post /api/ltcs-billings', async () => {
      const url = '/api/ltcs-billings'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Accepted)
      })

      await ltcsBillings.create({ form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'post', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/ltcs-billings').replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsBillings.create({ form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('file', () => {
    it('should get /api/ltcs-billings/:id/files/:token', async () => {
      const id = 1
      const token = 'x'.repeat(60)
      const url = `/api/ltcs-billings/${id}/files/${token}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, { url: 'https://www.example.com/file.pdf' })
      })

      await ltcsBillings.file({ id, token })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      const token = 'x'.repeat(60)
      const expected = {
        url: 'https://www.example.com/file.pdf'
      }
      adapter.setup(x => {
        x.onGet(`/api/ltcs-billings/${id}/files/${token}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await ltcsBillings.file({ id, token })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      const token = 'x'.repeat(60)
      adapter.setup(x => {
        x.onGet(`/api/ltcs-billings/${id}/files/${token}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsBillings.file({ id, token })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/ltcs-billings/:id', async () => {
      const id = 1
      const url = `/api/ltcs-billings/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createLtcsBillingResponseStub(id))
      })

      await ltcsBillings.get({ id })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      const response = createLtcsBillingResponseStub(id)
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onGet(`/api/ltcs-billings/${id}`).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await ltcsBillings.get({ id })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/ltcs-billings/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsBillings.get({ id })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    it('should get /api/ltcs-billings', async () => {
      const params = { page: 1 }
      const url = '/api/ltcs-billings'
      adapter.setup(x => {
        x.onGet(url).replyOnce(config => [HttpStatusCode.OK, createLtcsBillingIndexResponseStub(config.params)])
      })

      await ltcsBillings.getIndex(params)

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const params = { page: 2 }
      const expected = createLtcsBillingIndexResponseStub(params)
      adapter.setup(x => {
        x.onGet('/api/ltcs-billings').replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await ltcsBillings.getIndex(params)

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/ltcs-billings').replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsBillings.getIndex({ page: 4 })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('updateStatus', () => {
    const form: LtcsBillingsApi.UpdateStatusForm = {
      status: LtcsBillingStatus.fixed
    }

    it('should put /api/ltcs-billings/:id/status', async () => {
      const id = 10
      const url = `/api/ltcs-billings/${id}/status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createLtcsBillingResponseStub(id))
      })

      await ltcsBillings.updateStatus({ id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 11
      adapter.setup(x => {
        x.onPut(`/api/ltcs-billings/${id}/status`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsBillings.updateStatus({ id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const id = 12
      const response = createLtcsBillingResponseStub(id)
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(`/api/ltcs-billings/${id}/status`).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await ltcsBillings.updateStatus({ id, form })

      expect(actual).toStrictEqual(expected)
    })
  })
})
