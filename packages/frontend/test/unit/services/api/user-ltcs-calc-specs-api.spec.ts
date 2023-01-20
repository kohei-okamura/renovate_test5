/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsUserLocationAddition } from '@zinger/enums/lib/ltcs-user-location-addition'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { UserLtcsCalcSpecsApi } from '~/services/api/user-ltcs-calc-specs-api'
import { createUserLtcsCalcSpecResponseStub } from '~~/stubs/create-user-ltcs-calc-spec-response-stub'
import { createUserLtcsCalcSpecStub } from '~~/stubs/create-user-ltcs-calc-spec-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

type Form = DeepPartial<UserLtcsCalcSpecsApi.Form>
describe('api/user-ltcs-calc-specs-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let userLtcsCalcSpecs: UserLtcsCalcSpecsApi.Definition

  beforeEach(() => {
    userLtcsCalcSpecs = UserLtcsCalcSpecsApi.create(axios)
  })

  const form: Form = {
    effectivatedOn: '1995-01-20',
    locationAddition: LtcsUserLocationAddition.mountainousArea
  }

  describe('create', () => {
    const userId = 0
    it('should post /api/users/:userId/ltcs-calc-specs', async () => {
      const url = `/api/users/${userId}/ltcs-calc-specs`
      adapter.setup(x => {
        x.onPost().replyOnce(HttpStatusCode.Created)
      })

      await userLtcsCalcSpecs.create({ form, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/users/${userId}/ltcs-calc-specs`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = userLtcsCalcSpecs.create({ form, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/users/:userId/ltcs-calc-specs/:id', async () => {
      const id = 1
      const userId = 1
      const url = `/api/users/${userId}/ltcs-calc-specs/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createUserLtcsCalcSpecResponseStub(id))
      })

      await userLtcsCalcSpecs.get({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const stub = createUserLtcsCalcSpecStub()
      const id = stub.id
      const userId = stub.userId
      const expected = createUserLtcsCalcSpecResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/ltcs-calc-specs/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await userLtcsCalcSpecs.get({ id, userId })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      const userId = 2
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/ltcs-calc-specs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = userLtcsCalcSpecs.get({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const userId = 1

    it('should put /api/users/:userId/ltcs-calc-specs/:id', async () => {
      const id = 1
      const url = `/api/users/${userId}/ltcs-calc-specs/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK)
      })

      await userLtcsCalcSpecs.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 1
      const expected = createUserLtcsCalcSpecResponseStub(id)
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/ltcs-calc-specs/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await userLtcsCalcSpecs.update({ id, form, userId })

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/ltcs-calc-specs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })
      const promise = userLtcsCalcSpecs.update({ form, id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
