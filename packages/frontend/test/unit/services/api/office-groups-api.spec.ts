/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { createOfficeGroupIndexResponseStub } from '~~/stubs/create-office-group-index-response-stub'
import { createOfficeGroupResponseStub } from '~~/stubs/create-office-group-response-stub'
import { createOfficeGroupStub, createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/office-groups-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let officeGroups: OfficeGroupsApi.Definition

  beforeEach(() => {
    officeGroups = OfficeGroupsApi.create(axios)
  })

  describe('create', () => {
    const form: OfficeGroupsApi.Form = {
      name: '関東ブロック',
      parentOfficeGroupId: 30
    }

    it('should post /api/office-groups', async () => {
      const url = '/api/office-groups'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await officeGroups.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/office-groups').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = officeGroups.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('delete', () => {
    it('should delete /api/office-groups/:id', async () => {
      const id = 1
      const url = `/api/office-groups/${id}`
      adapter.setup(x => {
        x.onDelete(url).replyOnce(HttpStatusCode.NoContent)
      })

      await officeGroups.delete({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'delete', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onDelete(`/api/office-groups/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = officeGroups.delete({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/office-groups/:id', async () => {
      const id = 10
      const url = `/api/office-groups/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createOfficeGroupResponseStub(id))
      })

      await officeGroups.get({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 11
      const response = createOfficeGroupResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/office-groups/${id}`).replyOnce(HttpStatusCode.OK, response)
      })

      await expect(officeGroups.get({ id })).resolves.toStrictEqual(response)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 12
      adapter.setup(x => {
        x.onGet(`/api/office-groups/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = officeGroups.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const params: OfficeGroupsApi.GetIndexParams = {
      all: true
    }

    it('should get /api/office-groups', async () => {
      const url = '/api/office-groups'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createOfficeGroupIndexResponseStub())
      })

      await officeGroups.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onGet('/api/office-groups').replyOnce(HttpStatusCode.OK, createOfficeGroupIndexResponseStub())
      })

      const response = await officeGroups.getIndex(params)

      expect(response).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/office-groups').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = officeGroups.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('sort', () => {
    const params: OfficeGroupsApi.SortParams = {
      list: createOfficeGroupStubs()
    }

    it('should put /api/office-groups', async () => {
      const url = '/api/office-groups'
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createOfficeGroupIndexResponseStub())
      })

      await officeGroups.sort(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toMatchSnapshot()
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onPut('/api/office-groups').replyOnce(HttpStatusCode.OK, createOfficeGroupIndexResponseStub())
      })

      const response = await officeGroups.sort(params)

      expect(response).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPut('/api/office-groups').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = officeGroups.sort(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: OfficeGroupsApi.Form = {
      name: '関東ブロック',
      parentOfficeGroupId: 30
    }

    it('should put /api/office-groups/:id', async () => {
      const id = 1
      const url = `/api/office-groups/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await officeGroups.update({ id, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 11
      const officeGroup = createOfficeGroupStub(id)
      const expected = { officeGroup }
      const form = {
        name: officeGroup?.name,
        sortOrder: officeGroup?.sortOrder
      }

      adapter.setup(x => {
        x.onPut(`/api/office-groups/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await officeGroups.update({ id, form })
      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/office-groups/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = officeGroups.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
