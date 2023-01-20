/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { PermissionsApi } from '~/services/api/permissions-api'
import { createPermissionIndexResponseStub } from '~~/stubs/create-permission-index-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/permissions-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ltcsAreaGrades: PermissionsApi.Definition

  beforeEach(() => {
    ltcsAreaGrades = PermissionsApi.create(axios)
  })

  describe('getIndex', () => {
    const params: PermissionsApi.GetIndexParams = {
      all: true
    }

    it('should get /api/permissions', async () => {
      const url = '/api/permissions'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createPermissionIndexResponseStub())
      })

      await ltcsAreaGrades.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = createPermissionIndexResponseStub()
      adapter.setup(x => {
        x.onGet('/api/permissions').replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ltcsAreaGrades.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/permissions').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsAreaGrades.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
