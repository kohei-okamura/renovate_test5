/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { range } from '@zinger/helpers'
import { HttpStatusCode } from '~/models/http-status-code'
import { createJobStubState, StubFunction, updateJobStubState } from '~~/stubs/axios/utils'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'
import { createWithdrawalTransactionIndexResponseStub } from '~~/stubs/create-withdrawal-transaction-index-response-stub'

const baseUrl = '/api/withdrawal-transactions'

/**
 * 利用者請求 API をスタブ化する.
 */
export const stubWithdrawalTransactions: StubFunction = mockAdapter => {
  const importsJob = createJobStubState('withdrawalTransactionImports', /\/api\/jobs\/withdrawalTransactionImports.*/)

  mockAdapter
    .onGet(baseUrl).reply(config => [HttpStatusCode.OK, createWithdrawalTransactionIndexResponseStub(config.params)])

  // 口座振替データ作成
  const withdrawalJob = createJobStubState('withdrawalTransactions', /\/api\/jobs\/withdrawalTransactions.*/)
  // - 1度目はすぐにバリデーションエラーが返ってくる
  // - 2度目はしばらく待った後に失敗する
  // - 3度目以降は常に成功となる
  mockAdapter.onPost(`${baseUrl}`).replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { userBillingIds: '不正なidが含まれています。' } }
    ]
  })
  mockAdapter.onPost(`${baseUrl}`).reply(() => {
    updateJobStubState(withdrawalJob)
    return [HttpStatusCode.Created, createJobResponseStub(withdrawalJob.token, JobStatus.waiting)]
  })
  range(1, 2).forEach(() => mockAdapter.onGet(withdrawalJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(withdrawalJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(withdrawalJob.regex).replyOnce(() => {
    const content = createJobResponseStub(withdrawalJob.token, JobStatus.failure)
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(withdrawalJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(withdrawalJob.token, JobStatus.success)]
  })

  // 請求書ダウンロード
  const downloadJob = createJobStubState('downloadJob', /\/api\/jobs\/downloadJob.*/)
  mockAdapter.onPost('/api/withdrawal-transaction-files').reply(() => {
    updateJobStubState(downloadJob)
    return [HttpStatusCode.Created, createJobResponseStub(downloadJob.token, JobStatus.waiting)]
  })
  mockAdapter.onGet(downloadJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(downloadJob.token, JobStatus.success)]
  })

  // 一括登録時の処理
  // - 1度目はしばらく待った後に失敗する
  // - 2度目以降は常に成功となる
  mockAdapter.onPost('/api/withdrawal-transaction-imports').replyOnce(() => {
    return [
      HttpStatusCode.BadRequest,
      { errors: { file: ['不正なファイル形式です。'] } }
    ]
  })
  mockAdapter.onPost('/api/withdrawal-transaction-imports').reply(() => {
    updateJobStubState(importsJob)
    return [HttpStatusCode.Created, createJobResponseStub(importsJob.token, JobStatus.waiting)]
  })
  range(1, 2).forEach(() => mockAdapter.onGet(importsJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(importsJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(importsJob.regex).replyOnce(() => {
    const content = createJobResponseStub(importsJob.token, JobStatus.failure)
    const errors = [...Array(3)].map((_, i) => `何かが足りない気がする（行番号${i}）。`)
    Object.assign(content.job.data, { errors })
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(importsJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(importsJob.token, JobStatus.success)]
  })
}
