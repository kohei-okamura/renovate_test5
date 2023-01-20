/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import { SessionsApi } from '~/services/api/sessions-api'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/sessions-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let sessions: SessionsApi.Definition

  beforeEach(() => {
    sessions = SessionsApi.create(axios)
  })

  describe('create', () => {
    const auth: Auth = {
      isSystemAdmin: true,
      permissions: [],
      staff: createStaffStub()
    }
    const form: SessionsApi.Form = {
      email: 'john@example.com',
      password: 'PaSSWoRD',
      rememberMe: true
    }

    it('should post /api/sessions', async () => {
      const url = '/api/sessions'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created, { auth })
      })

      await sessions.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const expected = { auth }
      adapter.setup(x => {
        x.onPost('/api/sessions').replyOnce(HttpStatusCode.Created, expected)
      })

      const response = await sessions.create({ form })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/sessions').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = sessions.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('delete', () => {
    const url = '/api/sessions'

    it('should delete /api/sessions', async () => {
      adapter.setup(x => {
        x.onDelete(url).replyOnce(HttpStatusCode.NoContent)
      })

      await sessions.delete()

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'delete', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onDelete(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = sessions.delete()

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    const auth: Auth = {
      isSystemAdmin: true,
      permissions: [],
      staff: createStaffStub()
    }
    const url = '/api/sessions/my'

    it('should get /api/sessions', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.Created, { auth })
      })

      await sessions.get()

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const expected = { auth }
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await sessions.get()

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = sessions.get()

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
