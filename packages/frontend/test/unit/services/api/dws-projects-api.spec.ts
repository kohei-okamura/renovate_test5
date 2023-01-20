/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createDwsProjectResponseStub } from '~~/stubs/create-dws-project-response-stub'
import { createDwsProjectStub } from '~~/stubs/create-dws-project-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/dws-projects-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsProjects: DwsProjectsApi.Definition

  beforeEach(() => {
    dwsProjects = DwsProjectsApi.create(axios)
  })

  const form: DwsProjectsApi.Form = {
    officeId: 16,
    staffId: 70,
    writtenOn: '1972-07-22',
    effectivatedOn: '2014-10-20',
    requestFromUser: '中太麺だった。もやしに中太麺が好きだ。太麺も好きだ。細麺は高菜がほうれん草がちぢれ麺だった。細麺とサンマーメンにもやしの野菜が好きだ。魚粉がつけめんマシマシで。あっさりが好きだ。',
    requestFromFamily: 'わかめの替え玉の細麺も好きだ。太麺は味玉とこってりが好きだ。こってりはニンニクが味噌の味玉がちぢれ麺のわかめとチャーシューメンと細麺が好きだ。ネギが好きだ。高菜に太麺も好きだ。',
    objective: 'もやしに中太麺が好きだ。太麺も好きだ。細麺は高菜がほうれん草がちぢれ麺だった。細麺とサンマーメンにもやしの野菜が好きだ。魚粉がつけめんマシマシで。あっさりが好きだ。',
    programs: [
      {
        summaryIndex: 1,
        dayOfWeeks: [1, 2, 6, 7],
        category: 11,
        recurrence: 24,
        slot: { start: '04:30', end: '12:00' },
        options: [300003, 301102, 301104, 301105, 301106],
        contents: [
          { menuId: 27, duration: 97, memo: '海苔も好きだ。' },
          { menuId: 89, duration: 51, memo: 'サンマーメンが好きだ。' }
        ]
      },
      {
        summaryIndex: 2,
        dayOfWeeks: [1, 2, 7],
        category: 11,
        recurrence: 24,
        slot: { start: '08:30', end: '16:30' },
        options: [300003, 301102, 301104, 301105, 301106],
        contents: [
          { menuId: 96, duration: 43, memo: '冷やし中華を注文する。' },
          { menuId: 46, duration: 92, memo: 'チャーシューを注文する。' },
          { menuId: 91, duration: 25, memo: '中太麺が好きだ。' },
          { menuId: 81, duration: 14, memo: 'メンマも好きだ。' }
        ]
      }
    ]
  }

  describe('create', () => {
    const userId = 0

    it('should post /api/users/:userId/dws-projects', async () => {
      const url = `/api/users/${userId}/dws-projects`
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await dwsProjects.create({ form, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/users/${userId}/dws-projects`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsProjects.create({ form, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/users/:userId/dws-projects/:id', async () => {
      const id = 1
      const userId = 10
      const url = `/api/users/${userId}/dws-projects/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createDwsProjectResponseStub(id))
      })

      await dwsProjects.get({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const stub = createDwsProjectStub()
      const id = stub.id
      const userId = 10
      const expected = createDwsProjectResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-projects/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await dwsProjects.get({ id, userId })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      const userId = 12
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-projects/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsProjects.get({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const userId = 1

    it('should put /api/users/:userId/dws-projects/:id', async () => {
      const id = 1
      const url = `/api/users/${userId}/dws-projects/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await dwsProjects.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 1
      const userId = 1
      const contractId = 100
      const current = createDwsProjectResponseStub()
      const dwsProject = createDwsProjectStub(current.dwsProject.id, createContractStub(contractId))
      const expected = { dwsProject }
      const form = {
        officeId: dwsProject.officeId,
        staffId: dwsProject.staffId,
        writtenOn: dwsProject.writtenOn,
        effectivatedOn: dwsProject.effectivatedOn,
        requestFromUser: dwsProject.requestFromUser,
        requestFromFamily: dwsProject.requestFromFamily,
        programs: [...dwsProject.programs],
        objective: dwsProject.objective
      }

      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-projects/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await dwsProjects.update({ id, form, userId })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-projects/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsProjects.update({ form, id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
