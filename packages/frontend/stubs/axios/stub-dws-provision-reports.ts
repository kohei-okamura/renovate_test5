/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingServiceReportAggregateGroup } from '@zinger/enums/lib/dws-billing-service-report-aggregate-group'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { range, wait } from '@zinger/helpers'
import { HttpStatusCode } from '~/models/http-status-code'
import { createJobStubState, StubFunction, updateJobStubState } from '~~/stubs/axios/utils'
import { createDwsProvisionReportIndexResponseStub } from '~~/stubs/create-dws-provision-report-index-response-stub'
import { createDwsProvisionReportResponseStub } from '~~/stubs/create-dws-provision-report-response-stub'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'

const urlPattern = '/api/dws-provision-reports/\\d+\\/\\d+\\/\\d{4}-\\d{2}'

/**
 * 障害福祉サービス予実 API をスタブ化する.
 */
export const stubDwsProvisionReports: StubFunction = mockAdapter => {
  mockAdapter
    .onDelete(new RegExp(`${urlPattern}$`)).reply(HttpStatusCode.NoContent)
    .onGet('/api/dws-provision-reports').reply(({ params }) => {
      if (params.providedIn === '2021-04') {
        return [HttpStatusCode.OK, { list: [], pagination: {} }]
      }
      return [HttpStatusCode.OK, createDwsProvisionReportIndexResponseStub(params)]
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
      return [HttpStatusCode.OK, createDwsProvisionReportResponseStub({ id: +userId, providedIn })]
    })
    .onPut(new RegExp(`${urlPattern}$`)).reply(async ({ data, url }) => {
      // 400 を返すパターンの確認用
      const json = JSON.parse(data)
      if (json.results.length > 8) {
        await wait(1000)
        return [HttpStatusCode.BadRequest, {
          errors: {
            plans: '入力してください。',
            results: '入力してください。'
          }
        }]
      }
      const m = url!.match(/\/(\d{4}-\d{2})$/)
      const providedIn = m![1]
      return [HttpStatusCode.OK, createDwsProvisionReportResponseStub({ providedIn })]
    })
    .onPut(new RegExp(`${urlPattern}/status$`)).reply(async ({ url }) => {
      if (Math.floor(Math.random() * Math.floor(10)) % 3 !== 0) {
        await wait(1000)
        return [
          HttpStatusCode.BadRequest,
          { errors: { userId: ['契約に初回サービス提供日が設定されていないため確定できません。'] } }
        ]
      }
      const m = url!.match(/\/(\d{4}-\d{2})\/status$/)
      const providedIn = m![1]
      return [HttpStatusCode.OK, createDwsProvisionReportResponseStub({ providedIn })]
    })
    .onPost('/api/dws-provision-report-time-summary').reply(async () => {
      await wait(1000)
      const values = () => DwsBillingServiceReportAggregateGroup.values
        .filter(x => x !== DwsBillingServiceReportAggregateGroup.accessibleTaxi)
        // .map(x => ({ [x]: Math.floor(Math.random() * 300) * 10000 })) // 動作確認用
        .map(x => ({ [x]: 1000000 })) // テスト用（通常はこちら）
        .reduce((acc, cur) => ({ ...acc, ...cur }), {})
      const res = {
        plan: values(),
        result: values()
      }
      return [HttpStatusCode.OK, res]
    })
  // サービス提供実績記録票（プレビュー版）ダウンロードの処理
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  const job = createJobStubState('dwsProvisionReport', /\/api\/jobs\/dwsProvisionReport.*/)
  mockAdapter.onPost('/api/dws-service-report-previews').replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost('/api/dws-service-report-previews').reply(() => {
    updateJobStubState(job)
    return [HttpStatusCode.Accepted, createJobResponseStub(job.token, JobStatus.waiting)]
  })
  range(1, 3).forEach(() => mockAdapter.onGet(job.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(job.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(job.regex).replyOnce(() => {
    const content = createJobResponseStub(job.token, JobStatus.failure)
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(job.regex).reply(() => {
    const res = createJobResponseStub(job.token, JobStatus.success)
    Object.assign(res.job.data, {})
    return [HttpStatusCode.OK, res]
  })
}
