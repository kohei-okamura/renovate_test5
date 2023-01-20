/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { RolesApi } from '~/services/api/roles-api'
import { createRoleIndexResponseStub } from '~~/stubs/create-role-index-response-stub'
import { createRoleResponseStub } from '~~/stubs/create-role-response-stub'
import { createRoleStub } from '~~/stubs/create-role-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/roles-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let roles: RolesApi.Definition

  beforeEach(() => {
    roles = RolesApi.create(axios)
  })

  describe('create', () => {
    const form: RolesApi.Form = {
      permissions: {}
    }

    it('should post /api/roles', async () => {
      const url = '/api/roles'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await roles.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/roles').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = roles.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('delete', () => {
    it('should delete /api/roles/:id', async () => {
      const id = 1
      const url = `/api/roles/${id}`
      adapter.setup(x => {
        x.onDelete(url).replyOnce(HttpStatusCode.NoContent)
      })

      await roles.delete({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'delete', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onDelete(`/api/roles/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = roles.delete({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/roles/:id', async () => {
      const id = 1
      const url = `/api/roles/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createRoleResponseStub(id))
      })

      await roles.get({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      const expected = createRoleResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/roles/${id}`).replyOnce(HttpStatusCode.Created, expected)
      })

      const response = await roles.get({ id })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/roles/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = roles.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const params: RolesApi.GetIndexParams = {
      all: true
    }

    it('should get /api/roles', async () => {
      const url = '/api/roles'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createRoleIndexResponseStub())
      })

      await roles.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = createRoleIndexResponseStub()
      adapter.setup(x => {
        x.onGet('/api/roles').replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await roles.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/roles').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = roles.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: RolesApi.Form = {
      permissions: {}
    }

    it('should return response of the api', async () => {
      const id = 1
      const role = createRoleStub(id)
      const expected = { role }
      const form = {
        name: role.name,
        isSystemAdmin: role.isSystemAdmin,
        permissions: Object.fromEntries(role.permissions.map(x => [x, true])),
        scope: role.scope
      }

      adapter.setup(x => {
        x.onPut(`/api/roles/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await roles.update({ id, form })

      expect(response).toStrictEqual(expected)
    })

    it('should put /api/roles/:id', async () => {
      const id = 1
      const url = `/api/roles/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await roles.update({ id, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/roles/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = roles.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
