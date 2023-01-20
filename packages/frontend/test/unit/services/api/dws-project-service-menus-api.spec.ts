/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsProjectServiceMenusApi } from '~/services/api/dws-project-service-menus-api'
import { createDwsProjectServiceMenuStub } from '~~/stubs/create-dws-project-service-menu-stub'
import { createLtcsProjectResponseStub } from '~~/stubs/create-ltcs-project-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/dws-project-service-menus-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsProjectServiceMenus: DwsProjectServiceMenusApi.Definition

  beforeEach(() => {
    dwsProjectServiceMenus = DwsProjectServiceMenusApi.create(axios)
  })

  describe('getIndex', () => {
    it('should getIndex /api/dws-project-service-menus', async () => {
      const id = 1
      const url = '/api/dws-project-service-menus'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createDwsProjectServiceMenuStub(id))
      })

      await dwsProjectServiceMenus.getIndex({ all: true })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onGet('/api/dws-project-service-menus').replyOnce(HttpStatusCode.OK, createLtcsProjectResponseStub())
      })

      const response = await dwsProjectServiceMenus.getIndex({ all: true })

      expect(response).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/dws-project-service-menus').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsProjectServiceMenus.getIndex({ all: true })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
