/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers/index'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { WithdrawalTransactionsApi } from '~/services/api/withdrawal-transactions-api'
import { createWithdrawalTransactionIndexResponseStub } from '~~/stubs/create-withdrawal-transaction-index-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

const endpoint = 'withdrawal-transactions'

describe(`api/${endpoint}-api`, () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)

  let withdrawalTransactions: WithdrawalTransactionsApi.Definition

  beforeEach(() => {
    withdrawalTransactions = WithdrawalTransactionsApi.create(axios)
  })

  describe('getIndex', () => {
    it(`should get /api/${endpoint}`, async () => {
      const params = { page: 1 }
      const url = `/api/${endpoint}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(config => {
          return [HttpStatusCode.OK, createWithdrawalTransactionIndexResponseStub(config.params)]
        })
      })

      await withdrawalTransactions.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const params = { page: 2 }
      const expected = createWithdrawalTransactionIndexResponseStub(params)
      adapter.setup(x => {
        x.onGet(`/api/${endpoint}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await withdrawalTransactions.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(`/api/${endpoint}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = withdrawalTransactions.getIndex({ page: 4 })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('create', () => {
    const form: WithdrawalTransactionsApi.CreateForm = { userBillingIds: [1] }
    const url = `/api/${endpoint}`

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await withdrawalTransactions.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = withdrawalTransactions.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('import', () => {
    const file = new File(['TEST'], 'dummy')
    const form: any = { file }
    const url = '/api/withdrawal-transaction-imports'

    it('should post /api/withdrawal-transaction-imports', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await withdrawalTransactions.import({ form })

      const request = adapter.getLastRequest()
      const data = request.data
      assert(data instanceof FormData, 'data is not an instance of FormData')
      const file = data.get('file')
      assert(file instanceof File, 'file is not an instance of File')
      expect(request).toMatchObject({ method: 'post', url })
      expect(file.name).toBe('dummy')
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = withdrawalTransactions.import({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('download', () => {
    const form: WithdrawalTransactionsApi.DownloadForm = { id: 5 }
    const url = '/api/withdrawal-transaction-files'

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await withdrawalTransactions.download({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = withdrawalTransactions.download({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
