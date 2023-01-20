/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { range } from '@zinger/helpers'
import { HttpStatusCode } from '~/models/http-status-code'
import { createJobStubState, StubFunction, updateJobStubState } from '~~/stubs/axios/utils'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'
import { createShiftIndexResponseStub } from '~~/stubs/create-shift-index-response-stub'
import { createShiftResponseStub } from '~~/stubs/create-shift-response-stub'

/**
 * 勤務シフト API をスタブ化する.
 */
export const stubShifts: StubFunction = mockAdapter => {
  const cancelJob = createJobStubState('shiftsCancel', /\/api\/jobs\/shiftsCancel.*/)
  const confirmationJob = createJobStubState('shiftsConfirmation', /\/api\/jobs\/shiftsConfirmation.*/)
  const importsJob = createJobStubState('shiftImports', /\/api\/jobs\/shiftImports.*/)
  const templatesJob = createJobStubState('shiftTemplates', /\/api\/jobs\/shiftTemplates.*/)

  mockAdapter
    .onGet(/\/api\/shifts\/(\d+)$/).reply(config => {
      const m = config.url!.match(/\/(\d+)$/)
      const id = m && +m[1]
      return id ? [HttpStatusCode.OK, createShiftResponseStub(id)] : [HttpStatusCode.NotFound]
    })
    .onGet('/api/shifts').reply(config => [HttpStatusCode.OK, createShiftIndexResponseStub(config.params)])
    .onPost('/api/shifts').reply(HttpStatusCode.Created)
    .onPut(/\/api\/shifts\/\d+$/).reply(HttpStatusCode.NoContent)
    .onPost(/\/api\/shifts\/\d+\/cancel$/).reply(HttpStatusCode.NoContent)

  // 一括キャンセル時の処理
  // - 1度目はしばらく待った後に失敗する
  // - 2度目以降は常に成功となる
  mockAdapter.onPost('/api/shifts/cancel').reply(() => {
    updateJobStubState(cancelJob)
    return [HttpStatusCode.Created, createJobResponseStub(cancelJob.token, JobStatus.waiting)]
  })
  range(1, 2).forEach(() => mockAdapter.onGet(cancelJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(cancelJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(cancelJob.regex).replyOnce(() => {
    const content = createJobResponseStub(cancelJob.token, JobStatus.failure)
    const errors = [...Array(10)].map((_, i) => `何かが足りない気がする（行番号${i}）。`)
    Object.assign(content.job.data, { errors })
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(cancelJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(cancelJob.token, JobStatus.success)]
  })

  // 一括確定時の処理
  // - 1度目はしばらく待った後に失敗する
  // - 2度目以降は常に成功となる
  mockAdapter.onPost('/api/shifts/confirmation').reply(() => {
    updateJobStubState(confirmationJob)
    return [HttpStatusCode.Created, createJobResponseStub(confirmationJob.token, JobStatus.waiting)]
  })
  range(1, 3).forEach(() => mockAdapter.onGet(confirmationJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(confirmationJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(confirmationJob.regex).replyOnce(() => {
    const content = createJobResponseStub(confirmationJob.token, JobStatus.failure)
    const errors = [...Array(10)].map((_, i) => `何かが足りない気がする（行番号${i}）。`)
    Object.assign(content.job.data, { errors })
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(confirmationJob.regex).reply(() => {
    const res = createJobResponseStub(confirmationJob.token, JobStatus.success)
    Object.assign(res.job.data, {})
    return [HttpStatusCode.OK, res]
  })

  // 一括登録時の処理
  // - 1度目はしばらく待った後に失敗する
  // - 2度目以降は常に成功となる
  mockAdapter.onPost('/api/shift-imports').reply(() => {
    updateJobStubState(importsJob)
    return [HttpStatusCode.Created, createJobResponseStub(importsJob.token, JobStatus.waiting)]
  })
  range(1, 2).forEach(() => mockAdapter.onGet(importsJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(importsJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(importsJob.regex).replyOnce(() => {
    const content = createJobResponseStub(importsJob.token, JobStatus.failure)
    const errors = [...Array(300)].map((_, i) => `何かが足りない気がする（行番号${i}）。`)
    Object.assign(content.job.data, { errors })
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(importsJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(importsJob.token, JobStatus.success)]
  })

  // 一括登録用エクセルファイルダウンロードの処理
  mockAdapter.onPost('/api/shift-templates').reply(() => {
    updateJobStubState(templatesJob)
    return [HttpStatusCode.Created, createJobResponseStub(templatesJob.token, JobStatus.waiting)]
  })
  mockAdapter.onGet(templatesJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(templatesJob.token, JobStatus.success)]
  })
}
