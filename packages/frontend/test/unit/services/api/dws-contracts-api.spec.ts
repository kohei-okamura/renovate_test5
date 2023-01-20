/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import Axios from 'axios'
import { Range } from 'immutable'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'
import { createContractResponseStub } from '~~/stubs/create-contract-response-stub'
import { CONTRACT_ID_MAX, CONTRACT_ID_MIN, createContractStub } from '~~/stubs/create-contract-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/dws-contracts-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  const stubs = Range(CONTRACT_ID_MIN, CONTRACT_ID_MAX)
    .map(createContractStub)
    .filter(x => x.serviceSegment === ServiceSegment.disabilitiesWelfare)
    .take(10)
    .toArray()

  let contracts: DwsContractsApi.Definition

  beforeEach(() => {
    contracts = DwsContractsApi.create(axios)
  })

  describe('create', () => {
    const form: DwsContractsApi.CreateForm = {
      officeId: 9,
      note: '坊さんが屁をこいた'
    }

    it('should post /api/users/:userId/dws-contracts', async () => {
      const userId = 1
      const url = `/api/users/${userId}/dws-contracts`
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await contracts.create({ form, userId })
      const actual = adapter.getLastRequest()

      expect(actual).toMatchObject({ method: 'post', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const userId = 2
      adapter.setup(x => {
        x.onPost(`/api/users/${userId}/dws-contracts`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = contracts.create({ form, userId })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('disable', () => {
    it('should put /api/users/:userId/dws-contracts/:id', async () => {
      const id = 1
      const userId = 2
      const url = `/api/users/${userId}/dws-contracts/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await contracts.disable({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual({ status: ContractStatus.disabled })
    })

    it('should return response of the api', async () => {
      const { id, userId } = stubs[0]
      const expected = createContractResponseStub(id)
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-contracts/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await contracts.disable({ id, userId })

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 1
      const userId = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-contracts/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = contracts.disable({ id, userId })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/users/:userId/dws-contracts/:id', async () => {
      const id = 1
      const userId = 1
      const url = `/api/users/${userId}/dws-contracts/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createContractResponseStub(id))
      })

      await contracts.get({ id, userId })
      const actual = adapter.getLastRequest()

      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const { id, userId } = stubs[1]
      const expected = createContractResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-contracts/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await contracts.get({ id, userId })

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 1
      const userId = 2
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-contracts/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = contracts.get({ id, userId })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: DwsContractsApi.UpdateForm = {
      officeId: 9,
      status: ContractStatus.terminated,
      contractedOn: '1995-01-20',
      terminatedOn: '1999-01-20',
      dwsPeriods: {
        [DwsServiceDivisionCode.homeHelpService]: {
          start: '1995-02-01',
          end: '1995-12-31'
        },
        [DwsServiceDivisionCode.visitingCareForPwsd]: {
          start: '1996-01-01',
          end: '1999-01-15'
        }
      },
      note: 'だるまさんがころんだ'
    }

    it('should put /api/users/:userId/dws-contracts/:id', async () => {
      const id = 1
      const userId = 2
      const url = `/api/users/${userId}/dws-contracts/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await contracts.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const { id, userId } = stubs[2]
      const expected = createContractResponseStub(id)
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-contracts/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const actual = await contracts.update({ id, form, userId })

      expect(actual).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 1
      const userId = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-contracts/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = contracts.update({ form, id, userId })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
