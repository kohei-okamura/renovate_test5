/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'
import { createLtcsSubsidyResponseStub } from '~~/stubs/create-ltcs-subsidy-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/ltcs-subsidy-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ltcsSubsidy: LtcsSubsidiesApi.Definition

  beforeEach(() => {
    ltcsSubsidy = LtcsSubsidiesApi.create(axios)
  })

  const form: LtcsSubsidiesApi.Form = {
    period: {
      start: '1985-11-28T21:20:21+0900',
      end: '2013-01-25T19:17:44+0900'
    },
    defrayerCategory: 81,
    defrayerNumber: '12019738',
    recipientNumber: '1149255',
    benefitRate: 73,
    copay: 1904
  }

  describe('create', () => {
    const userId = 0

    it('should post /api/users/:userId/ltcs-subsidies', async () => {
      const url = `/api/users/${userId}/ltcs-subsidies`
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await ltcsSubsidy.create({ form, userId })
      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/users/${userId}/ltcs-subsidies`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsSubsidy.create({ form, userId })
      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })

    describe('delete', () => {
      const userId = 2
      const id = 1

      it('should delete /api/users/:userId/ltcs-subsidies/:id', async () => {
        const url = `/api/users/${userId}/ltcs-subsidies/${id}`
        adapter.setup(x => {
          x.onDelete(url).replyOnce(HttpStatusCode.NoContent)
        })

        await ltcsSubsidy.delete({ id, userId })

        const request = adapter.getLastRequest()
        expect(request).toMatchObject({ method: 'delete', url })
      })

      it('should throw an AxiosError when failed to request', async () => {
        adapter.setup(x => {
          x.onDelete(`/api/users/${userId}/ltcs-subsidies/${id}`).replyOnce(HttpStatusCode.BadRequest)
          x.onDelete(`/api/users/${userId}/ltcs-subsidies/${id}`).replyOnce(HttpStatusCode.BadRequest)
          x.onDelete(`/api/users/${userId}/ltcs-subsidies/${id}`).replyOnce(HttpStatusCode.BadRequest)
        })

        const promise = ltcsSubsidy.delete({ id, userId })

        await expect(promise).rejects.toThrowErrorMatchingSnapshot()
      })
    })

    describe('get', () => {
      it('should get /api/users/:userId/ltcs-subsidies/:id', async () => {
        const id = 1
        const userId = 10
        const url = `/api/users/${userId}/ltcs-subsidies/${id}`
        adapter.setup(x => {
          x.onGet(url).replyOnce(HttpStatusCode.OK, createLtcsSubsidyResponseStub(id))
        })

        await ltcsSubsidy.get({ id, userId })

        const request = adapter.getLastRequest()
        expect(request).toMatchObject({ method: 'get', url })
      })

      it('should throw an AxiosError when failed to request', async () => {
        const id = 3
        const userId = 12
        adapter.setup(x => {
          x.onGet(`/api/users/${userId}/ltcs-subsidies/${id}`).replyOnce(HttpStatusCode.BadRequest)
        })

        const promise = ltcsSubsidy.get({ id, userId })

        await expect(promise).rejects.toThrowErrorMatchingSnapshot()
      })
    })
  })

  describe('update', () => {
    const userId = 1

    it('should put /api/users/:userId/ltcs-subsidies/:id', async () => {
      const id = 1
      const url = `/api/users/${userId}/ltcs-subsidies/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await ltcsSubsidy.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/ltcs-subsidies/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsSubsidy.update({ form, id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
