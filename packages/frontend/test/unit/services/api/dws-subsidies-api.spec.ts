/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'
import { createDwsSubsidyResponseStub } from '~~/stubs/create-dws-subsidy-response-stub'
import { createDwsSubsidyStub } from '~~/stubs/create-dws-subsidy-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/dws-subsidies-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsSubsidies: DwsSubsidiesApi.Definition

  beforeEach(() => {
    dwsSubsidies = DwsSubsidiesApi.create(axios)
  })

  const form: DwsSubsidiesApi.Form = {
    period: {
      start: '1985-11-28T21:20:21+0900',
      end: '2013-01-25T19:17:44+0900'
    },
    cityName: '東伯郡琴浦町',
    cityCode: '34033',
    subsidyType: 1,
    factor: UserDwsSubsidyFactor.copay,
    benefitRate: 50,
    copayRate: 0,
    rounding: Rounding.floor,
    benefitAmount: 0,
    copayAmount: 0,
    note: 'タンメンにしよう。チャーシューの、チャーシューはタマネギのもやしだった。油そばは、塩が雲呑がタンメンを注文する。チャーシューメンも好きだ。油そばが海苔がちぢれ麺とタンメンが好きだ。'
  }

  describe('create', () => {
    const userId = 0
    const url = `/api/users/${userId}/dws-subsidies`

    it('should post /api/users/:userId/dws-subsidies', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await dwsSubsidies.create({ form, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsSubsidies.create({ form, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    const userId = 10

    it('should get /api/users/:userId/dws-subsidies/:id', async () => {
      const id = 1
      const url = `/api/users/${userId}/dws-subsidies/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createDwsSubsidyResponseStub(id))
      })

      await dwsSubsidies.get({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const stub = createDwsSubsidyStub()
      const id = stub.id
      const expected = createDwsSubsidyResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-subsidies/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await dwsSubsidies.get({ id, userId })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-subsidies/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsSubsidies.get({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const id = 1
    const userId = 1
    const url = `/api/users/${userId}/dws-subsidies/${id}`

    it('should put /api/users/:userId/dws-subsidies/:id', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await dwsSubsidies.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const current = createDwsSubsidyResponseStub()
      const dwsSubsidy = createDwsSubsidyStub(current.dwsSubsidy.id)
      const expected = { dwsSubsidy }
      const form: DwsSubsidiesApi.Form = {
        period: dwsSubsidy.period,
        cityName: dwsSubsidy.cityName,
        cityCode: dwsSubsidy.cityCode,
        subsidyType: dwsSubsidy.subsidyType,
        factor: dwsSubsidy.factor,
        benefitRate: dwsSubsidy.benefitRate,
        copayRate: dwsSubsidy.copayRate,
        rounding: dwsSubsidy.rounding,
        benefitAmount: dwsSubsidy.benefitAmount,
        copayAmount: dwsSubsidy.copayAmount,
        note: dwsSubsidy.note
      }

      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await dwsSubsidies.update({ form, id, userId })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsSubsidies.update({ form, id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
