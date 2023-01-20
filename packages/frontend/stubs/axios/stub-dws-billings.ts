/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { range } from '@zinger/helpers'
import { AxiosRequestConfig } from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { createJobStubState, StubFunction, updateJobStubState } from '~~/stubs/axios/utils'
import { createDwsBillingIndexResponseStub } from '~~/stubs/create-dws-billing-index-response-stub'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createDwsBillingStatementResponseStub } from '~~/stubs/create-dws-billing-statement-response-stub'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'

const statementUrlPattern = '/api/dws-billings/\\d+/bundles/\\d+/statements/\\d+'

/**
 * 障害福祉サービス請求 API をスタブ化する.
 */
export const stubDwsBillings: StubFunction = mockAdapter => {
  const getBillingFunction = (config: AxiosRequestConfig) => {
    const m = config.url!.match(/\/(\d+)$/)
    const id = m && m.length >= 2 ? +m[1] : undefined
    return [HttpStatusCode.OK, createDwsBillingResponseStub(id)]
  }
  const getStatementFunction = (config: AxiosRequestConfig, endpoint = '') => {
    const regex = new RegExp(`/(\\d+)${endpoint}$`)
    const m = config.url!.match(regex)
    const id = m && m.length >= 2 ? +m[1] : undefined
    return [HttpStatusCode.OK, createDwsBillingStatementResponseStub({ id })]
  }
  mockAdapter
    .onGet(/\/api\/dws-billings\/(\d+)$/).reply(getBillingFunction)
    .onGet('/api/dws-billings').reply(config => [HttpStatusCode.OK, createDwsBillingIndexResponseStub(config.params)])
    .onPut(/\/api\/dws-billings\/\d+\/status$/).reply(getBillingFunction)
    .onGet(/\/api\/dws-billings\/\d+\/files\/[a-zA-Z0-9]{60}$/).reply(HttpStatusCode.OK, {
      url: '/example.xlsx'
    })
    .onGet(new RegExp(`${statementUrlPattern}$`)).reply(getStatementFunction)
    .onPut(new RegExp(`${statementUrlPattern}$`)).reply(getStatementFunction)
    .onPut(new RegExp(`${statementUrlPattern}/copay-coordination$`)).reply(config => getStatementFunction(config, '/copay-coordination'))
    .onPut(new RegExp(`${statementUrlPattern}/copay-coordination-status$`)).reply(config => getStatementFunction(config, '/copay-coordination-status'))
    .onPut(new RegExp(`${statementUrlPattern}/status$`)).reply(config => getStatementFunction(config, '/status'))

  // 作成時の処理
  // - 1度目はしばらく待った後に失敗する
  // - 2度目以降は常に成功となる
  const job = createJobStubState('dwsBilling', /\/api\/jobs\/dwsBilling.*/)
  mockAdapter.onPost('api/dws-billings').reply(() => {
    updateJobStubState(job)
    return [HttpStatusCode.Created, createJobResponseStub(job.token, JobStatus.waiting)]
  })
  range(1, 2).forEach(() => mockAdapter.onGet(job.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(job.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(job.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(job.token, JobStatus.failure)]
  })
  mockAdapter.onGet(job.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(job.token, JobStatus.success)]
  })

  // 明細書状態一括更新の処理
  const updateBulkStatusJob = createJobStubState('bulkStatus', /\/api\/jobs\/bulkStatus.*/)
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/statement-status-update$/).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/statement-status-update$/).reply(() => {
    updateJobStubState(updateBulkStatusJob)
    return [HttpStatusCode.Created, createJobResponseStub(updateBulkStatusJob.token, JobStatus.waiting)]
  })
  range(1, 3).forEach(() => mockAdapter.onGet(updateBulkStatusJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(updateBulkStatusJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(updateBulkStatusJob.regex).replyOnce(() => {
    const content = createJobResponseStub(updateBulkStatusJob.token, JobStatus.failure)
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(updateBulkStatusJob.regex).reply(() => {
    const res = createJobResponseStub(updateBulkStatusJob.token, JobStatus.success)
    Object.assign(res.job.data, {})
    return [HttpStatusCode.OK, res]
  })

  // 実績記録票状態一括更新の処理
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/service-report-status-update$/).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/service-report-status-update$/).reply(() => {
    updateJobStubState(updateBulkStatusJob)
    return [HttpStatusCode.Created, createJobResponseStub(updateBulkStatusJob.token, JobStatus.waiting)]
  })
  range(1, 3).forEach(() => mockAdapter.onGet(updateBulkStatusJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(updateBulkStatusJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(updateBulkStatusJob.regex).replyOnce(() => {
    const content = createJobResponseStub(updateBulkStatusJob.token, JobStatus.failure)
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(updateBulkStatusJob.regex).reply(() => {
    const res = createJobResponseStub(updateBulkStatusJob.token, JobStatus.success)
    Object.assign(res.job.data, {})
    return [HttpStatusCode.OK, res]
  })

  // 明細書リフレッシュの処理
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/statement-refresh$/).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/statement-refresh$/).reply(() => {
    updateJobStubState(job)
    return [HttpStatusCode.Created, createJobResponseStub(job.token, JobStatus.waiting)]
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

  // 請求コピーの処理
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/copy$/).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/copy$/).reply(() => {
    updateJobStubState(job)
    return [HttpStatusCode.Created, createJobResponseStub(job.token, JobStatus.waiting)]
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
