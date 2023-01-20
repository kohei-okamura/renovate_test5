/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { range } from '@zinger/helpers'
import { AxiosRequestConfig } from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { createJobStubState, StubFunction, updateJobStubState } from '~~/stubs/axios/utils'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'
import { createLtcsBillingIndexResponseStub } from '~~/stubs/create-ltcs-billing-index-response-stub'
import { createLtcsBillingResponseStub } from '~~/stubs/create-ltcs-billing-response-stub'
import { createLtcsBillingStatementResponseStub } from '~~/stubs/create-ltcs-billing-statement-response-stub'

/**
 * 介護保険サービス請求 API をスタブ化する.
 */
export const stubLtcsBillings: StubFunction = mockAdapter => {
  const getBillingFunction = (config: AxiosRequestConfig) => {
    const m = config.url!.match(/\/(\d+)$/)
    const id = m && m.length >= 2 ? +m[1] : undefined
    return [HttpStatusCode.OK, createLtcsBillingResponseStub(id)]
  }
  const getStatementFunction = (config: AxiosRequestConfig) => {
    const m = config.url!.match(/\/statements\/(\d+)$/)
    const id = m && m.length >= 2 ? +m[1] : undefined
    return [HttpStatusCode.OK, createLtcsBillingStatementResponseStub({ id })]
  }
  mockAdapter
    .onGet(/\/api\/ltcs-billings\/\d+$/).reply(getBillingFunction)
    .onGet('/api/ltcs-billings').reply(config => [HttpStatusCode.OK, createLtcsBillingIndexResponseStub(config.params)])
    .onPut(/\/api\/ltcs-billings\/\d+\/status$/).reply(getBillingFunction)
    .onGet(/\/api\/ltcs-billings\/\d+\/files\/[a-zA-Z0-9]{60}$/).reply(HttpStatusCode.OK, {
      url: '/example.xlsx'
    })
    .onGet(/\/api\/ltcs-billings\/\d+\/bundles\/\d+\/statements\/\d+$/).reply(getStatementFunction)
    .onPut(/\/api\/ltcs-billings\/\d+\/bundles\/\d+\/statements\/\d+$/).reply(getStatementFunction)
    .onPut(/\/api\/ltcs-billings\/\d+\/bundles\/\d+\/statements\/\d+\/status$/).reply(getStatementFunction)

  // 作成時の処理
  // - 1度目はしばらく待った後に失敗する
  // - 2度目以降は常に成功となる
  const job = createJobStubState('ltcsBilling', /\/api\/jobs\/ltcsBilling.*/)
  mockAdapter.onPost('/api/ltcs-billings').reply(() => {
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
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(/\/api\/ltcs-billings\/\d+\/bundles\/\d+\/statements\/bulk-status$/).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost(/\/api\/ltcs-billings\/\d+\/bundles\/\d+\/statements\/bulk-status$/).reply(() => {
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

  // 明細書リフレッシュの処理
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(/\/api\/ltcs-billings\/\d+\/statement-refresh$/).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost(/\/api\/ltcs-billings\/\d+\/statement-refresh$/).reply(() => {
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
