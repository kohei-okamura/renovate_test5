/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsAreaGradesApi } from '~/services/api/dws-area-grades-api'
import { createDwsAreaGradeIndexResponseStub } from '~~/stubs/create-dws-area-grade-index-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/dws-area-grades-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsAreaGrades: DwsAreaGradesApi.Definition

  beforeEach(() => {
    dwsAreaGrades = DwsAreaGradesApi.create(axios)
  })

  describe('getIndex', () => {
    const params: DwsAreaGradesApi.GetIndexParams = {
      all: true
    }

    it('should get /api/dws-area-grades', async () => {
      const url = '/api/dws-area-grades'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createDwsAreaGradeIndexResponseStub())
      })

      await dwsAreaGrades.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = createDwsAreaGradeIndexResponseStub()
      adapter.setup(x => {
        x.onGet('/api/dws-area-grades').replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await dwsAreaGrades.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/dws-area-grades').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsAreaGrades.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
