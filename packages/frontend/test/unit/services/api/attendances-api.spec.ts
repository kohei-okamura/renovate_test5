/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { AttendancesApi } from '~/services/api/attendances-api'
import { $datetime } from '~/services/datetime-service'
import { createAttendanceIndexResponseStub } from '~~/stubs/create-attendance-index-response-stub'
import { createAttendanceResponseStub } from '~~/stubs/create-attendance-response-stub'
import { ATTENDANCE_ID_MIN, createAttendanceStub } from '~~/stubs/create-attendance-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/attendances-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let attendances: AttendancesApi.Definition

  beforeEach(() => {
    attendances = AttendancesApi.create(axios)
  })

  describe('confirm', () => {
    const ids = [1, 2, 3, 4, 5]
    const url = '/api/attendances/confirmation'

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await attendances.confirm({ ids })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual({ ids })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = attendances.confirm({ ids })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('create', () => {
    const form: any = {}
    const url = '/api/attendances'

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await attendances.create({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = attendances.create({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('batchCancel', () => {
    const params = { ids: [1, 2, 3, 4, 5], reason: '担当者が体調不良のため。' }
    const url = '/api/attendances/cancel'

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.NoContent)
      })

      await attendances.batchCancel(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual({ params })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = attendances.batchCancel(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('cancel', () => {
    const id = 1
    const params = { id, reason: '先方から急に外出することになったと連絡があったため。' }
    const url = `/api/attendances/${id}/cancel`

    it('should post /api/attendances/:id/cancel', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.NoContent)
      })

      await attendances.cancel(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = attendances.cancel(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/attendances/:id', async () => {
      const id = ATTENDANCE_ID_MIN + 1
      const url = `/api/attendances/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createAttendanceResponseStub(id))
      })

      await attendances.get({ id })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const id = ATTENDANCE_ID_MIN + 2
      const expected = createAttendanceResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/attendances/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await attendances.get({ id })
      // toStrictEqual だと undefined が差異としてあつかわれるため objectContaining を使用する
      expect(response).toEqual(expect.objectContaining(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = ATTENDANCE_ID_MIN + 3
      adapter.setup(x => {
        x.onGet(`/api/attendances/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = attendances.get({ id })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('getIndex', () => {
    const url = '/api/attendances'

    it(`should get ${url}`, async () => {
      const params = { page: 1 }
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createAttendanceIndexResponseStub())
      })

      await attendances.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', params, url })
    })

    it('should return response of the api', async () => {
      const expected = createAttendanceIndexResponseStub()
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await attendances.getIndex()
      // toStrictEqual だと undefined が差異としてあつかわれるため objectContaining を使用する
      expect(response).toEqual(expect.objectContaining(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = attendances.getIndex({ page: 4 })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: any = {}

    it('should put /api/attendances/:id', async () => {
      const id = 1
      const url = `/api/attendances/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await attendances.update({ id, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 1
      const attendance = createAttendanceStub(id, createContractStub())
      const expected = { attendance }
      const form = {
        task: attendance.task,
        serviceCode: attendance.serviceCode,
        officeId: attendance.officeId,
        userId: attendance.userId,
        contractId: attendance.contractId,
        assignerId: attendance.assignerId,
        assignees: attendance.assignees,
        headcount: attendance.headcount,
        schedule: {
          date: $datetime.parse(attendance.schedule.date).toISODate(),
          start: $datetime.parse(attendance.schedule.start).toFormat('HH:mm'),
          end: $datetime.parse(attendance.schedule.end).toFormat('HH:mm')
        },
        durations: [...attendance.durations],
        options: [...attendance.options],
        note: attendance.note
      }

      adapter.setup(x => {
        x.onPut(`/api/attendances/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await attendances.update({ id, form })
      expect(response).toEqual(expect.objectContaining(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/attendances/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = attendances.update({ id, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
