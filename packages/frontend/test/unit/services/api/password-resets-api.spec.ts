/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { PasswordResetsApi } from '~/services/api/password-resets-api'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/password-resets-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let passwordResets: PasswordResetsApi.Definition

  beforeEach(() => {
    passwordResets = PasswordResetsApi.create(axios)
  })

  describe('commit', () => {
    const form: PasswordResetsApi.CommitForm = {
      password: 'p@ssw0rd'
    }
    const token = 'x'.repeat(60)

    it('should put /api/password-resets/:token', async () => {
      const url = `/api/password-resets/${token}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await passwordResets.commit({ form, token })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPut(`/api/password-resets/${token}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = passwordResets.commit({ form, token })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('create', () => {
    const form = {
      email: 'smith@example.jp'
    }

    it('should post /api/password-resets', async () => {
      const url = '/api/password-resets'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await passwordResets.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/password-resets').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = passwordResets.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('verify', () => {
    const token = 'x'.repeat(60)
    const url = `/api/password-resets/${token}`

    it('should get /api/password-resets/:token', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.NoContent)
      })

      await passwordResets.verify({ token })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.NotFound)
      })

      const promise = passwordResets.verify({ token })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
