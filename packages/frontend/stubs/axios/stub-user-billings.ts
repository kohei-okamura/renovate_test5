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
import { createUserBillingIndexResponseStub } from '~~/stubs/create-user-billing-index-response-stub'
import { createUserBillingResponseStub } from '~~/stubs/create-user-billing-response-stub'

const baseUrl = '/api/user-billings'

/**
 * 利用者請求 API をスタブ化する.
 */
export const stubUserBillings: StubFunction = mockAdapter => {
  const getBillingFunction = (config: AxiosRequestConfig) => {
    const m = config.url!.match(/\/(\d+)$/)
    const id = m && +m[1]
    return id ? [HttpStatusCode.OK, createUserBillingResponseStub(id)] : [HttpStatusCode.NotFound]
  }
  mockAdapter
    .onGet(/\/api\/user-billings\/(\d+)$/).reply(getBillingFunction)
    .onGet(baseUrl).reply(config => [HttpStatusCode.OK, createUserBillingIndexResponseStub(config.params)])
    .onPut(/\/api\/user-billings\/(\d+)$/).reply(getBillingFunction)

  // 請求書ダウンロード
  const invoicesJob = createJobStubState('user-billing-invoices', /\/api\/jobs\/user-billing-invoices.*/)
  mockAdapter.onPost('/api/user-billing-invoices').replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost('/api/user-billing-invoices').reply(() => {
    updateJobStubState(invoicesJob)
    return [HttpStatusCode.Created, createJobResponseStub(invoicesJob.token, JobStatus.waiting)]
  })
  mockAdapter.onGet(invoicesJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(invoicesJob.token, JobStatus.success)]
  })
  // 代理受領額通知書ダウンロード
  const noticesJob = createJobStubState('user-billing-notices', /\/api\/jobs\/user-billing-notices.*/)
  mockAdapter.onPost('/api/user-billing-notices').replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost('/api/user-billing-notices').reply(() => {
    updateJobStubState(noticesJob)
    return [HttpStatusCode.Created, createJobResponseStub(noticesJob.token, JobStatus.waiting)]
  })
  mockAdapter.onGet(noticesJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(noticesJob.token, JobStatus.success)]
  })
  // 領収書ダウンロード
  const receiptsJob = createJobStubState('user-billing-receipts', /\/api\/jobs\/user-billing-receipts.*/)
  mockAdapter.onPost('/api/user-billing-receipts').replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost('/api/user-billing-receipts').reply(() => {
    updateJobStubState(receiptsJob)
    return [HttpStatusCode.Created, createJobResponseStub(receiptsJob.token, JobStatus.waiting)]
  })
  mockAdapter.onGet(receiptsJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(receiptsJob.token, JobStatus.success)]
  })
  // 介護サービス利用明細書ダウンロード
  const statementsJob = createJobStubState('user-billing-statements', /\/api\/jobs\/user-billing-statements.*/)
  mockAdapter.onPost('/api/user-billing-statements').replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost('/api/user-billing-statements').reply(() => {
    updateJobStubState(statementsJob)
    return [HttpStatusCode.Created, createJobResponseStub(statementsJob.token, JobStatus.waiting)]
  })
  mockAdapter.onGet(statementsJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(statementsJob.token, JobStatus.success)]
  })

  // 入金日一括登録時の処理
  const registrationJob = createJobStubState('depositRegistration', /\/api\/jobs\/depositRegistration.*/)
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(`${baseUrl}/deposit-registration`).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'], depositedOn: ['入金日が不正です。'] } }
    ]
  })
  mockAdapter.onPost(`${baseUrl}/deposit-registration`).reply(() => {
    updateJobStubState(registrationJob)
    return [HttpStatusCode.Created, createJobResponseStub(registrationJob.token, JobStatus.waiting)]
  })
  range(1, 2).forEach(() => mockAdapter.onGet(registrationJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(registrationJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(registrationJob.regex).replyOnce(() => {
    const content = createJobResponseStub(registrationJob.token, JobStatus.failure)
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(registrationJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(registrationJob.token, JobStatus.success)]
  })

  // 入金日一括削除の処理
  const cancellationJob = createJobStubState('depositCancellation', /\/api\/jobs\/depositCancellation.*/)
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(`${baseUrl}/deposit-cancellation`).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { ids: ['不正なidが含まれています。'] } }
    ]
  })
  mockAdapter.onPost(`${baseUrl}/deposit-cancellation`).reply(() => {
    updateJobStubState(cancellationJob)
    return [HttpStatusCode.Created, createJobResponseStub(cancellationJob.token, JobStatus.waiting)]
  })
  range(1, 3).forEach(() => mockAdapter.onGet(cancellationJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(cancellationJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(cancellationJob.regex).replyOnce(() => {
    const content = createJobResponseStub(cancellationJob.token, JobStatus.failure)
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(cancellationJob.regex).reply(() => {
    const res = createJobResponseStub(cancellationJob.token, JobStatus.success)
    Object.assign(res.job.data, {})
    return [HttpStatusCode.OK, res]
  })
}
