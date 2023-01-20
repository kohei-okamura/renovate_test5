/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { StaffsApi } from '~/services/api/staffs-api'
import { createStaffIndexResponseStub } from '~~/stubs/create-staff-index-response-stub'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/staffs-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let staffs: StaffsApi.Definition

  beforeEach(() => {
    staffs = StaffsApi.create(axios)
  })

  describe('create', () => {
    const form: Partial<StaffsApi.CreateForm> = {
      password: 'PaSSWoRD'
    }

    it('should post /api/staffs', async () => {
      const url = '/api/staffs'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await staffs.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/staffs').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = staffs.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/staffs/:id', async () => {
      const id = 1
      const url = `/api/staffs/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createStaffResponseStub(id))
      })

      await staffs.get({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      adapter.setup(x => {
        x.onGet(`/api/staffs/${id}`).replyOnce(HttpStatusCode.OK, createStaffResponseStub(id))
      })

      const response = await staffs.get({ id })

      expect(response).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/staffs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = staffs.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const params: StaffsApi.GetIndexParams = {
      all: true
    }

    it('should get /api/staffs', async () => {
      const url = '/api/staffs'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createStaffIndexResponseStub())
      })

      await staffs.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = createStaffIndexResponseStub()
      adapter.setup(x => {
        x.onGet('/api/staffs').replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await staffs.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/staffs').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = staffs.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: Partial<StaffsApi.UpdateForm> = {}

    it('should put /api/staffs/:id', async () => {
      const id = 1
      const url = `/api/staffs/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await staffs.update({ id, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 1
      const staff = createStaffStub(id)
      const expected = { staff }
      const form = {
        familyName: staff.name.familyName,
        givenName: staff.name.givenName,
        phoneticFamilyName: staff.name.phoneticFamilyName,
        phoneticGivenName: staff.name.phoneticGivenName,
        sex: staff.sex,
        birthday: staff.birthday,
        postcode: staff.addr.postcode,
        prefecture: staff.addr.prefecture,
        city: staff.addr.city,
        street: staff.addr.street,
        apartment: staff.addr.apartment,
        tel: staff.tel,
        fax: staff.fax,
        email: staff.email,
        certifications: staff.certifications
      }

      adapter.setup(x => {
        x.onPut(`/api/staffs/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await staffs.update({ id, form })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/staffs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = staffs.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('verify', () => {
    it('should put /api/staff-verifications/:token', async () => {
      const token = 'x'.repeat(60)
      const url = `/api/staff-verifications/${token}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await staffs.verify({ token })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toMatchObject({ isVerified: true })
    })

    it('should throw an AxiosError when failed to request', async () => {
      const token = 'x'.repeat(60)
      adapter.setup(x => {
        x.onPut(`/api/staff-verifications/${token}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = staffs.verify({ token })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
