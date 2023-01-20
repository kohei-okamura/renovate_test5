/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { createUserBillingIndexResponseStub } from '~~/stubs/create-user-billing-index-response-stub'
import { createUserBillingResponseStub } from '~~/stubs/create-user-billing-response-stub'
import { createUserBillingUserStub } from '~~/stubs/create-user-billing-user-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

const endpoint = 'user-billings'

describe(`api/${endpoint}-api`, () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)

  let userBillings: UserBillingsApi.Definition

  beforeEach(() => {
    userBillings = UserBillingsApi.create(axios)
  })

  describe('get', () => {
    it(`should get /api/${endpoint}/:id`, async () => {
      const id = 1
      const url = `/api/${endpoint}/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createUserBillingResponseStub(id))
      })

      await userBillings.get({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      const expected = createUserBillingResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/${endpoint}/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await userBillings.get({ id })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/${endpoint}/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = userBillings.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    it(`should get /api/${endpoint}`, async () => {
      const params = { page: 1 }
      const url = `/api/${endpoint}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(config => [HttpStatusCode.OK, createUserBillingIndexResponseStub(config.params)])
      })

      await userBillings.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const params = { page: 2 }
      const expected = createUserBillingIndexResponseStub(params)
      adapter.setup(x => {
        x.onGet(`/api/${endpoint}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await userBillings.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(`/api/${endpoint}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = userBillings.getIndex({ page: 4 })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: UserBillingsApi.UpdateForm = {
      bankAccount: createUserBillingUserStub().bankAccount,
      carriedOverAmount: 1234,
      paymentMethod: PaymentMethod.collection
    }
    it(`should update /api/${endpoint}/:id`, async () => {
      const id = 1
      const url = `/api/${endpoint}/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createUserBillingResponseStub(id))
      })

      await userBillings.update({ id, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      const expected = createUserBillingResponseStub(id)
      adapter.setup(x => {
        x.onPut(`/api/${endpoint}/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await userBillings.update({ id, form })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onPut(`/api/${endpoint}/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = userBillings.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe.each<string, keyof UserBillingsApi.Download>([
    ['invoices', 'downloadInvoices'],
    ['notices', 'downloadNotices'],
    ['receipts', 'downloadReceipts'],
    ['statements', 'downloadStatements']
  ])('download %s', (type, fnName) => {
    const form: UserBillingsApi.DownloadForm = {
      ids: [1, 2, 3, 4, 5],
      issuedOn: '2021-11-10T00:00:00Z'
    }
    const url = `/api/user-billing-${type}`

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await userBillings[fnName]({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = userBillings[fnName]({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
