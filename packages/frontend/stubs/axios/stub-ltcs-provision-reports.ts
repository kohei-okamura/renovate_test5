/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { wait } from '@zinger/helpers'
import { HttpStatusCode } from '~/models/http-status-code'
import { createJobStubState, StubFunction, updateJobStubState } from '~~/stubs/axios/utils'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'
import { createLtcsProvisionReportIndexResponseStub } from '~~/stubs/create-ltcs-provision-report-index-response-stub'
import { createLtcsProvisionReportResponseStub } from '~~/stubs/create-ltcs-provision-report-response-stub'

const urlPattern = '/api/ltcs-provision-reports/\\d+\\/\\d+\\/\\d{4}-\\d{2}'

const sheetsJob = createJobStubState('ltcs-provision-report-sheets', /\/api\/jobs\/ltcs-provision-report-sheets.*/)

/**
 * 介護保険サービス予実 API をスタブ化する.
 */
export const stubLtcsProvisionReports: StubFunction = mockAdapter => mockAdapter
  .onDelete(new RegExp(`${urlPattern}$`)).reply(HttpStatusCode.NoContent)
  .onGet('/api/ltcs-provision-reports').reply(({ params }) => {
    if (params.providedIn === '2021-04') {
      return [HttpStatusCode.OK, { list: [], pagination: {} }]
    }
    return [HttpStatusCode.OK, createLtcsProvisionReportIndexResponseStub(params)]
  })
  .onGet(new RegExp(`${urlPattern}$`)).reply(({ url }) => {
    const m = url!.match(/\/(\d+)\/(\d{4}-\d{2})$/)
    const userId = m![1]
    // 404 を返すパターンの確認用
    // 当面は邪魔なのでコメントアウト
    // if (parseInt(userId) % 3 === 0) {
    //   return [HttpStatusCode.NotFound]
    // }
    const providedIn = m![2]
    return [HttpStatusCode.OK, createLtcsProvisionReportResponseStub({ id: +userId, providedIn })]
  })
  .onPut(new RegExp(`${urlPattern}$`)).reply(async ({ data, url }) => {
    // 400 を返すパターンの確認用
    const json = JSON.parse(data)
    if (json.entries.length > 2) {
      await wait(1000)
      return [HttpStatusCode.BadRequest, {
        errors: {
          entries: '入力してください。'
        }
      }]
    }
    const m = url!.match(/\/(\d{4}-\d{2})$/)
    const providedIn = m![1]
    return [HttpStatusCode.OK, createLtcsProvisionReportResponseStub({ providedIn })]
  })
  .onPut(new RegExp(`${urlPattern}/status$`)).reply(async ({ url }) => {
    if (Math.floor(Math.random() * Math.floor(10)) % 3 !== 0) {
      await wait(1000)
      return [HttpStatusCode.BadRequest, {
        errors: {
          userId: ['契約に初回サービス提供日が設定されていないため確定できません。']
        }
      }]
    }
    const m = url!.match(/\/(\d{4}-\d{2})\/status$/)
    const providedIn = m![1]
    return [HttpStatusCode.OK, createLtcsProvisionReportResponseStub({ providedIn })]
  })
  .onPost('/api/ltcs-provision-report-score-summary').reply(async () => {
    await wait(1000)
    const fixed = true
    const score = (value: number) => fixed ? value : Math.floor(Math.random() * 10000)
    const res = {
      plan: {
        managedScore: score(6000),
        unmanagedScore: score(4000)
      },
      result: {
        managedScore: score(5800),
        unmanagedScore: score(6200)
      }
    }
    return [HttpStatusCode.OK, res]
  })
  // サービス提供票ダウンロード
  .onPost('/api/ltcs-provision-report-sheets').reply(() => {
    updateJobStubState(sheetsJob)
    return [HttpStatusCode.Created, createJobResponseStub(sheetsJob.token, JobStatus.waiting)]
  })
  .onGet(sheetsJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(sheetsJob.token, JobStatus.success)]
  })
