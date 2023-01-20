/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsUserLocationAddition } from '@zinger/enums/lib/dws-user-location-addition'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'
import { createUserDwsCalcSpecResponseStub } from '~~/stubs/create-user-dws-calc-spec-response-stub'
import { createUserDwsCalcSpecStub } from '~~/stubs/create-user-dws-calc-spec-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

type Form = DeepPartial<UserDwsCalcSpecsApi.Form>
describe('api/user-dws-calc-specs-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let userDwsCalcSpecs: UserDwsCalcSpecsApi.Definition

  beforeEach(() => {
    userDwsCalcSpecs = UserDwsCalcSpecsApi.create(axios)
  })

  const form: Form = {
    effectivatedOn: '1995-01-20',
    locationAddition: DwsUserLocationAddition.specifiedArea
  }

  describe('create', () => {
    const userId = 0
    it('should post /api/users/:userId/dws-calc-specs', async () => {
      const url = `/api/users/${userId}/dws-calc-specs`
      adapter.setup(x => {
        x.onPost().replyOnce(HttpStatusCode.Created)
      })

      await userDwsCalcSpecs.create({ form, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/users/${userId}/dws-calc-specs`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = userDwsCalcSpecs.create({ form, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/users/:userId/dws-calc-specs/:id', async () => {
      const id = 1
      const userId = 1
      const url = `/api/users/${userId}/dws-calc-specs/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createUserDwsCalcSpecResponseStub(id))
      })

      await userDwsCalcSpecs.get({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const stub = createUserDwsCalcSpecStub()
      const id = stub.id
      const userId = stub.userId
      const expected = createUserDwsCalcSpecResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-calc-specs/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await userDwsCalcSpecs.get({ id, userId })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      const userId = 2
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-calc-specs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = userDwsCalcSpecs.get({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const userId = 1

    it('should put /api/users/:userId/dws-calc-specs/:id', async () => {
      const id = 1
      const url = `/api/users/${userId}/dws-calc-specs/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK)
      })

      await userDwsCalcSpecs.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 1
      const expected = createUserDwsCalcSpecResponseStub(id)
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-calc-specs/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await userDwsCalcSpecs.update({ id, form, userId })

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-calc-specs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })
      const promise = userDwsCalcSpecs.update({ form, id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
