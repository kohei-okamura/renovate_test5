/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsProjectServiceMenusApi } from '~/services/api/ltcs-project-service-menus-api'
import { createLtcsProjectResponseStub } from '~~/stubs/create-ltcs-project-response-stub'
import { createLtcsProjectServiceMenuStub } from '~~/stubs/create-ltcs-project-service-menu-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/ltcs-project-service-menus-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ltcsProjectServiceMenus: LtcsProjectServiceMenusApi.Definition

  beforeEach(() => {
    ltcsProjectServiceMenus = LtcsProjectServiceMenusApi.create(axios)
  })

  describe('getIndex', () => {
    it('should getIndex /api/ltcs-project-service-menus', async () => {
      const id = 1
      const url = '/api/ltcs-project-service-menus'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createLtcsProjectServiceMenuStub(id))
      })

      await ltcsProjectServiceMenus.getIndex({ all: true })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onGet('/api/ltcs-project-service-menus').replyOnce(HttpStatusCode.OK, createLtcsProjectResponseStub())
      })

      const response = await ltcsProjectServiceMenus.getIndex({ all: true })

      expect(response).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/ltcs-project-service-menus').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProjectServiceMenus.getIndex({ all: true })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
