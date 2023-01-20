/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { createOwnExpenseProgramIndexResponseStub } from '~~/stubs/create-own-expense-program-index-response-stub'
import { createOwnExpenseProgramResponseStub } from '~~/stubs/create-own-expense-program-response-stub'
import { createOwnExpenseProgramStub } from '~~/stubs/create-own-expense-program-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/own-expense-program-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ownExpensePrograms: OwnExpenseProgramsApi.Definition

  beforeEach(() => {
    ownExpensePrograms = OwnExpenseProgramsApi.create(axios)
  })

  describe('create', () => {
    const { officeId, name, durationMinutes, fee, note } = createOwnExpenseProgramStub()
    const form: OwnExpenseProgramsApi.Form = {
      officeId: officeId || 20,
      name,
      durationMinutes,
      fee,
      note
    }
    const url = '/api/own-expense-programs'

    it('should post /api/own-expense-programs', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await ownExpensePrograms.create({ form })

      const request = adapter.getLastRequest()

      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ownExpensePrograms.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/own-expense-programs/:id', async () => {
      const id = 1
      const url = `/api/own-expense-programs/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createOwnExpenseProgramResponseStub(id))
      })

      await ownExpensePrograms.get({ id })

      const request = adapter.getLastRequest()

      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = 2
      const expected = createOwnExpenseProgramResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/own-expense-programs/${id}`).replyOnce(HttpStatusCode.OK, createOwnExpenseProgramResponseStub(id))
      })

      const response = await ownExpensePrograms.get({ id })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      adapter.setup(x => {
        x.onGet(`/api/own-expense-programs/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ownExpensePrograms.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const params: OwnExpenseProgramsApi.GetIndexParams = {
      all: true
    }

    it('should get /api/own-expense-program', async () => {
      const url = '/api/own-expense-programs'
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createOwnExpenseProgramIndexResponseStub(params))
      })

      await ownExpensePrograms.getIndex(params)
      const request = adapter.getLastRequest()

      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onGet('/api/own-expense-programs').replyOnce(HttpStatusCode.OK, createOwnExpenseProgramIndexResponseStub(params))
      })

      const result = await ownExpensePrograms.getIndex(params)

      expect(result).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet('/api/own-expense-programs').replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ownExpensePrograms.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const { officeId, name, durationMinutes, fee, note } = createOwnExpenseProgramStub()
    const form: OwnExpenseProgramsApi.Form = {
      officeId: officeId || 10,
      name,
      durationMinutes,
      fee,
      note
    }
    const id = 1
    const url = `/api/own-expense-programs/${id}`

    it('should put /api/own-expense-programs/:id', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await ownExpensePrograms.update({ form, id })

      const request = adapter.getLastRequest()

      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ownExpensePrograms.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const ownExpenseProgram = createOwnExpenseProgramStub(id)
      const expected = { ownExpenseProgram }
      const form = {
        name: ownExpenseProgram.name,
        durationMinutes: ownExpenseProgram.durationMinutes,
        fee: ownExpenseProgram.fee,
        note: ownExpenseProgram.note
      }

      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ownExpensePrograms.update({ form, id })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })
  })
})
