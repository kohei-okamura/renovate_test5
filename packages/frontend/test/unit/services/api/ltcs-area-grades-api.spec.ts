/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsAreaGradesApi } from '~/services/api/ltcs-area-grades-api'
import { createLtcsAreaGradeIndexResponseStub } from '~~/stubs/create-ltcs-area-grade-index-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/ltcs-area-grades-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ltcsAreaGrades: LtcsAreaGradesApi.Definition

  beforeEach(() => {
    ltcsAreaGrades = LtcsAreaGradesApi.create(axios)
  })

  describe('getIndex', () => {
    const params: LtcsAreaGradesApi.GetIndexParams = {
      all: true
    }

    it('should get /api/ltcs-area-grades', async () => {
      const url = '/api/ltcs-area-grades'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createLtcsAreaGradeIndexResponseStub())
      })

      await ltcsAreaGrades.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = createLtcsAreaGradeIndexResponseStub()
      adapter.setup(x => {
        x.onGet('/api/ltcs-area-grades').replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ltcsAreaGrades.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/ltcs-area-grades').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsAreaGrades.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
