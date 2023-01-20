/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ContactRelationship } from '@zinger/enums/lib/contact-relationship'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { UsersApi } from '~/services/api/users-api'
import { createUserIndexResponseStub } from '~~/stubs/create-user-index-response-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/users-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  const form: UsersApi.Form = {
    familyName: '出川',
    givenName: '哲朗',
    phoneticFamilyName: 'デガワ',
    phoneticGivenName: 'テツロウ',
    sex: Sex.male,
    birthday: '1964-02-13',
    postcode: '105-0014',
    prefecture: Prefecture.tokyo,
    city: '港区',
    street: '芝3-24-3',
    apartment: 'SUN TORA ビル 4F',
    contacts: [{
      tel: '090-1234-5678',
      relationship: ContactRelationship.theirself
    }],
    isEnabled: true,
    billingDestination: {
      destination: 1,
      paymentMethod: 1,
      contractNumber: '7599599805',
      corporationName: 'デイサービス土屋 中野中央',
      agentName: '新井 恵梨香',
      addr: {
        postcode: '545-0034',
        prefecture: 27,
        city: '大阪市阿倍野区',
        street: '阿倍野元町2-6-12',
        apartment: ''
      },
      tel: '0731-85-3606'
    }
  }

  let users: UsersApi.Definition

  beforeEach(() => {
    users = UsersApi.create(axios)
  })

  describe('create', () => {
    it('should post /api/users', async () => {
      const url = '/api/users'
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await users.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost('/api/users').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = users.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/users/:id', async () => {
      const id = 1
      const url = `/api/users/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createUserResponseStub(id))
      })

      await users.get({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      const expected = createUserResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await users.get({ id })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/users/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = users.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    it('should get /api/users', async () => {
      const params = { page: 1 }
      const url = '/api/users'
      adapter.setup(x => {
        x.onGet(url).replyOnce(config => [HttpStatusCode.OK, createUserIndexResponseStub(config.params)])
      })

      await users.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const params = { page: 2 }
      const expected = createUserIndexResponseStub(params)
      adapter.setup(x => {
        x.onGet('/api/users').replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await users.getIndex(params)

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/users').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = users.getIndex({ page: 4 })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    it('should put /api/users/:id', async () => {
      const id = 1
      const url = `/api/users/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await users.update({ id, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = users.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  it('should return response of the api', async () => {
    const id = 1
    const user = createUserStub(id)
    const expected = { user }
    const form = {
      familyName: user.name.familyName,
      givenName: user.name.givenName,
      phoneticFamilyName: user.name.phoneticFamilyName,
      phoneticGivenName: user.name.phoneticGivenName,
      sex: user.sex,
      birthday: user.birthday,
      postcode: user.addr.postcode,
      city: user.addr.city,
      street: user.addr.street,
      apartment: user.addr.apartment,
      contacts: user.contacts
    }

    adapter.setup(x => {
      x.onPut(`/api/users/${id}`).replyOnce(HttpStatusCode.OK, expected)
    })

    const response = await users.update({ id, form })

    expect(response).toStrictEqual(expected)
  })
})
