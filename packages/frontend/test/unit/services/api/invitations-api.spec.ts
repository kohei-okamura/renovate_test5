/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { InvitationsApi } from '~/services/api/invitations-api'
import { createInvitationStub } from '~~/stubs/create-invitation-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/invitations-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let invitations: InvitationsApi.Definition

  beforeEach(() => {
    invitations = InvitationsApi.create(axios)
  })

  describe('create', () => {
    const form: Partial<InvitationsApi.Form> = {}

    it('should post /api/invitations', async () => {
      const url = '/api/invitations'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await invitations.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/invitations').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = invitations.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/invitations/:token', async () => {
      const token = 'a'.repeat(60)
      const url = `/api/invitations/${token}`
      adapter.setup(x => {
        const id = 1
        const invitation = createInvitationStub(id, token)
        x.onGet(url).replyOnce(HttpStatusCode.OK, { invitation })
      })

      await invitations.get({ token })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const token = 'b'.repeat(60)
      const url = `/api/invitations/${token}`
      adapter.setup(x => {
        const id = 2
        const invitation = createInvitationStub(id, token)
        x.onGet(url).replyOnce(HttpStatusCode.OK, { invitation })
      })

      const response = await invitations.get({ token })

      expect(response).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      const token = 'c'.repeat(60)
      adapter.setup(x => {
        x.onGet(`/api/invitations/${token}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = invitations.get({ token })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
