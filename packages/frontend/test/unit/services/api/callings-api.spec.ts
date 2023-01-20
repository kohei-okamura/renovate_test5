/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { CallingsApi } from '~/services/api/callings-api'
import { createShiftIndexResponseStub } from '~~/stubs/create-shift-index-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/callings-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let callings: CallingsApi.Definition

  beforeEach(() => {
    callings = CallingsApi.create(axios)
  })

  describe('acknowledge', () => {
    const token = 'x'.repeat(60)
    const url = `/api/callings/${token}/acknowledges`
    it('should post /api/callings/:token/acknowledges', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.NoContent)
      })

      await callings.acknowledge(token)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = callings.acknowledge(token)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const token = 'x'.repeat(60)
    const url = `/api/callings/${token}/shifts`

    it('should get /api/callings/:token/shifts', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createShiftIndexResponseStub())
      })

      await callings.getIndex(token)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const expected = createShiftIndexResponseStub()
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, expected)
      })
      const response = await callings.getIndex(token)
      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = callings.getIndex(token)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
