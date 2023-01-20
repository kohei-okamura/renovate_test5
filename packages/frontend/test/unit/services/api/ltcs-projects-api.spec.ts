/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createLtcsProjectResponseStub } from '~~/stubs/create-ltcs-project-response-stub'
import { createLtcsProjectStub } from '~~/stubs/create-ltcs-project-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/ltcs-projects-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ltcsProjects: LtcsProjectsApi.Definition

  beforeEach(() => {
    ltcsProjects = LtcsProjectsApi.create(axios)
  })

  const form: LtcsProjectsApi.Form = {
    contractId: 3,
    officeId: 16,
    staffId: 70,
    writtenOn: '1972-07-22',
    effectivatedOn: '2014-10-20',
    problem: '雲呑はもやしマシマシで。替え玉が食べたい。焼きラーメンの、味噌も好きだ。塩は背脂はサンマーメンが好きだ。紅ショウガは醤油はワンタンも好きだ。魚介系にしよう。細麺が好きだ。つけめんマシマシで。',
    requestFromUser: '中太麺だった。もやしに中太麺が好きだ。太麺も好きだ。細麺は高菜がほうれん草がちぢれ麺だった。細麺とサンマーメンにもやしの野菜が好きだ。魚粉がつけめんマシマシで。あっさりが好きだ。',
    requestFromFamily: 'わかめの替え玉の細麺も好きだ。太麺は味玉とこってりが好きだ。こってりはニンニクが味噌の味玉がちぢれ麺のわかめとチャーシューメンと細麺が好きだ。ネギが好きだ。高菜に太麺も好きだ。',
    longTermObjective: {
      term: {
        start: '1981-09-07T00:51:44+0900',
        end: '1997-03-13T05:59:56+0900'
      },
      text: '野菜が好きだ。鶏白湯マシマシで。細麺がわかめがチャーシューが食べたい。もやしが好きだ。あっさりはメンマに、野菜が好きだ。味玉にしよう。紅ショウガと細麺の醤油も好きだ。ニンニクが食べたい。'
    },
    shortTermObjective: {
      term: {
        start: '2019-05-28T02:08:12+0900',
        end: '2023-02-26T05:57:31+0900'
      },
      text: '味玉にちぢれ麺だった。冷やし中華は雲呑が食べたい。背脂が高菜マシマシで。ワンタンの野菜だった。高菜が紅ショウガマシマシで。高菜にチャーシューはサンマーメンは野菜とタマネギマシマシで。'
    },
    programs: [
      {
        programIndex: 1,
        category: 13,
        recurrence: 13,
        timeframe: 1,
        dayOfWeeks: [3, 5],
        slot: { start: '20:55', end: '22:15' },
        amounts: [
          { category: 11, amount: 55 },
          { category: 12, amount: 25 }
        ],
        headcount: 1,
        serviceCode: '2358979791',
        options: [100002, 401101],
        contents: [
          { menuId: 57, duration: 60, content: '焼豚マシマシで。', memo: '海苔が食べたい。' },
          { menuId: 96, duration: 45, content: '', memo: '' }
        ]
      }
    ]
  }

  describe('create', () => {
    const userId = 0

    it('should post /api/users/:userId/ltcs-projects', async () => {
      const url = `/api/users/${userId}/ltcs-projects`
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await ltcsProjects.create({ form, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/users/${userId}/ltcs-projects`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProjects.create({ form, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/users/:userId/ltcs-projects/:id', async () => {
      const id = 1
      const userId = 10
      const url = `/api/users/${userId}/ltcs-projects/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createLtcsProjectResponseStub(id))
      })

      await ltcsProjects.get({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const stub = createLtcsProjectStub()
      const id = stub.id
      const userId = 10
      const expected = createLtcsProjectResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/ltcs-projects/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ltcsProjects.get({ id, userId })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 3
      const userId = 12
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/ltcs-projects/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProjects.get({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const userId = 1

    it('should put /api/users/:userId/ltcs-projects/:id', async () => {
      const id = 1
      const url = `/api/users/${userId}/ltcs-projects/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await ltcsProjects.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 1
      const userId = 1
      const contractId = 100
      const current = createLtcsProjectResponseStub()
      const ltcsProject = createLtcsProjectStub(current.ltcsProject.id, createContractStub(contractId))
      const expected = { ltcsProject }
      const form = {
        officeId: ltcsProject.officeId,
        staffId: ltcsProject.staffId,
        writtenOn: ltcsProject.writtenOn,
        effectivatedOn: ltcsProject.effectivatedOn,
        requestFromUser: ltcsProject.requestFromUser,
        requestFromFamily: ltcsProject.requestFromFamily,
        problem: ltcsProject.problem,
        programs: [...ltcsProject.programs],
        shortTermObjective: ltcsProject.shortTermObjective,
        longTermObjective: ltcsProject.longTermObjective
      }

      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/ltcs-projects/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ltcsProjects.update({ id, form, userId })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/ltcs-projects/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProjects.update({ form, id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
