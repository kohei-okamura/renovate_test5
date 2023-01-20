/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assign } from '@zinger/helpers'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { PostcodeApi } from '~/services/api/postcode-api'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/postcode-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  const postcodeResolverURL = 'https://postcode.eustylelab.ninja/'
  let postcode: PostcodeApi.Definition

  beforeAll(() => {
    process.env = assign(process.env, { postcodeResolverURL })
  })

  beforeEach(() => {
    postcode = PostcodeApi.create(axios)
  })

  describe('get', () => {
    it(`should get ${postcodeResolverURL}###/#######.json`, async () => {
      const url = `${postcodeResolverURL}164/1640011.json`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, [])
      })

      await postcode.get({ postcode: '164-0011' })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should not request to api server when valid postcode is not given', async () => {
      const url = `${postcodeResolverURL}123/1234567.json`
      adapter.setup(x => {
        x.onGet(url).reply(HttpStatusCode.OK, [])
      })

      await postcode.get({ postcode: '' })
      await postcode.get({ postcode: '1' })
      await postcode.get({ postcode: '12' })
      await postcode.get({ postcode: '123' })
      await postcode.get({ postcode: '1234' })
      await postcode.get({ postcode: '12345' })
      await postcode.get({ postcode: '123456' })
      await postcode.get({ postcode: '1234567' })
      await postcode.get({ postcode: '123-4567' })
      await postcode.get({ postcode: '123--4567' })

      const requests = adapter.getRequests()
      expect(requests).toHaveLength(2)
      expect(requests[0]).toMatchObject({ method: 'get', url })
      expect(requests[1]).toMatchObject({ method: 'get', url })
    })

    it('should return empty array when api server responses 403 Forbidden', async () => {
      adapter.setup(x => {
        x.onGet(`${postcodeResolverURL}164/1640011.json`).replyOnce(HttpStatusCode.Forbidden)
      })

      const xs = await postcode.get({ postcode: '164-0011' })

      expect(xs).toBeEmptyArray()
    })

    it('should return empty array when api server responses 404 NotFound', async () => {
      adapter.setup(x => {
        x.onGet(`${postcodeResolverURL}164/1640011.json`).replyOnce(HttpStatusCode.NotFound)
      })

      const xs = await postcode.get({ postcode: '164-0011' })

      expect(xs).toBeEmptyArray()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(`${postcodeResolverURL}164/1640011.json`).replyOnce(HttpStatusCode.InternalServerError)
      })

      const promise = postcode.get({ postcode: '164-0011' })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
