/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { ShiftsApi } from '~/services/api/shifts-api'
import { $datetime } from '~/services/datetime-service'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createShiftIndexResponseStub } from '~~/stubs/create-shift-index-response-stub'
import { createShiftResponseStub } from '~~/stubs/create-shift-response-stub'
import { createShiftStub, SHIFT_ID_MIN } from '~~/stubs/create-shift-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/shifts-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let shifts: ShiftsApi.Definition

  beforeEach(() => {
    shifts = ShiftsApi.create(axios)
  })

  describe('confirm', () => {
    const ids = [1, 2, 3, 4, 5]
    const url = '/api/shifts/confirmation'

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await shifts.confirm({ ids })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual({ ids })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.confirm({ ids })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('create', () => {
    const form: any = {}
    const url = '/api/shifts'

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await shifts.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('createTemplate', () => {
    const form: any = {}
    const url = '/api/shift-templates'

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await shifts.createTemplate({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.createTemplate({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('batchCancel', () => {
    const params = { ids: [1, 2, 3, 4, 5], reason: '担当者が体調不良のため。' }
    const url = '/api/shifts/cancel'

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.NoContent)
      })

      await shifts.batchCancel(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual({ params })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.batchCancel(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('cancel', () => {
    const id = 1
    const params = { id, reason: '先方から急に外出することになったと連絡があったため。' }
    const url = `/api/shifts/${id}/cancel`

    it('should post /api/shifts/:id/cancel', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.NoContent)
      })

      await shifts.cancel(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.cancel(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/shifts/:id', async () => {
      const id = SHIFT_ID_MIN + 1
      const url = `/api/shifts/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createShiftResponseStub(id))
      })

      await shifts.get({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = SHIFT_ID_MIN + 2
      const expected = createShiftResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/shifts/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await shifts.get({ id })
      // toStrictEqual だと undefined が差異としてあつかわれるため objectContaining を使用する
      expect(response).toEqual(expect.objectContaining(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = SHIFT_ID_MIN + 3
      adapter.setup(x => {
        x.onGet(`/api/shifts/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const url = '/api/shifts'

    it('should get /api/shifts', async () => {
      const params = { page: 1 }
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createShiftIndexResponseStub())
      })

      await shifts.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = createShiftIndexResponseStub()
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await shifts.getIndex()
      // toStrictEqual だと undefined が差異としてあつかわれるため objectContaining を使用する
      expect(response).toEqual(expect.objectContaining(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.getIndex({ page: 4 })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('import', () => {
    const file = new File(['TEST'], 'dummy.xlsx')
    const form: any = { file }
    const url = '/api/shift-imports'

    it('should post /api/shift-imports', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await shifts.import({ form })

      const request = adapter.getLastRequest()
      const data = request.data
      assert(data instanceof FormData, 'data is not an instance of FormData')
      const file = data.get('file')
      assert(file instanceof File, 'file is not an instance of File')
      expect(request).toMatchObject({ method: 'post', url })
      expect(file.name).toBe('dummy.xlsx')
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.import({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: any = {}

    it('should put /api/shifts/:id', async () => {
      const id = 1
      const url = `/api/shifts/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await shifts.update({ id, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 1
      const shift = createShiftStub(id, createContractStub())
      const expected = { shift }
      const form = {
        task: shift.task,
        serviceCode: shift.serviceCode,
        officeId: shift.officeId,
        userId: shift.userId,
        contractId: shift.contractId,
        assignerId: shift.assignerId,
        assignees: shift.assignees,
        headcount: shift.headcount,
        schedule: {
          date: $datetime.parse(shift.schedule.date).toISODate(),
          start: $datetime.parse(shift.schedule.start).toFormat('HH:mm'),
          end: $datetime.parse(shift.schedule.end).toFormat('HH:mm')
        },
        durations: [...shift.durations],
        options: [...shift.options],
        note: shift.note
      }

      adapter.setup(x => {
        x.onPut(`/api/shifts/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await shifts.update({ id, form })

      expect(response).toEqual(expect.objectContaining(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/shifts/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = shifts.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
