/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsHomeVisitLongTermCareDictionaryApi } from '~/services/api/ltcs-home-visit-long-term-care-dictionary-api'
import { createUserIndexResponseStub } from '~~/stubs/create-user-index-response-stub'
import { createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub } from '~~/stubs/ltcs-home-visit-long-term-care-dictionary-entry-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/ltcs-home-visit-long-term-care-dictionary-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)

  let dictionary: LtcsHomeVisitLongTermCareDictionaryApi.Definition

  beforeEach(() => {
    dictionary = LtcsHomeVisitLongTermCareDictionaryApi.create(axios)
  })

  describe('getIndex', () => {
    it('should get /api/ltcs-home-visit-long-term-care-dictionary', async () => {
      const params = { officeId: 111, isEffectiveOn: '2021-01-01' }
      const url = '/api/ltcs-home-visit-long-term-care-dictionary'
      adapter.setup(x => {
        x.onGet(url).replyOnce(config => [HttpStatusCode.OK, createUserIndexResponseStub(config.params)])
      })

      await dictionary.getIndex(params)

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const params = { officeId: 222, isEffectiveOn: '2021-02-01' }
      const expected = createUserIndexResponseStub(params)
      adapter.setup(x => {
        x.onGet('/api/ltcs-home-visit-long-term-care-dictionary').replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await dictionary.getIndex(params)

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const params = { officeId: 333, isEffectiveOn: '2021-03-01' }
      adapter.setup(x => {
        x.onGet('/api/ltcs-home-visit-long-term-care-dictionary').replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dictionary.getIndex(params)

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })
  describe('get', () => {
    it('should get /api/ltcs-home-visit-long-term-care-dictionary-entries/:serviceCode', async () => {
      const serviceCode = '111111'
      const queryParams = { providedIn: '2021-01' }
      const url = `/api/ltcs-home-visit-long-term-care-dictionary-entries/${serviceCode}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(
          HttpStatusCode.OK,
          createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub(serviceCode)
        )
      })

      await dictionary.get({ serviceCode, ...queryParams })

      const request = adapter.getLastRequest()

      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const serviceCode = '111112'
      const queryParams = { providedIn: '2021-02' }
      const expected = createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub(serviceCode)
      adapter.setup(x => {
        x.onGet(`/api/ltcs-home-visit-long-term-care-dictionary-entries/${serviceCode}`).replyOnce(
          HttpStatusCode.OK,
          createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub(serviceCode)
        )
      })

      const response = await dictionary.get({ serviceCode, ...queryParams })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected!))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const serviceCode = '111113'
      const queryParams = { providedIn: '2021-03' }
      adapter.setup(x => {
        x.onGet(`/api/ltcs-home-visit-long-term-care-dictionary-entries/${serviceCode}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dictionary.get({ serviceCode, ...queryParams })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
