/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { range } from '@zinger/helpers'
import { HttpStatusCode } from '~/models/http-status-code'
import { createJobStubState, StubFunction, updateJobStubState } from '~~/stubs/axios/utils'
import { createAttendanceIndexResponseStub } from '~~/stubs/create-attendance-index-response-stub'
import { createAttendanceResponseStub } from '~~/stubs/create-attendance-response-stub'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'

/**
 * 勤務実績 API をスタブ化する.
 */
export const stubAttendances: StubFunction = mockAdapter => {
  const cancelJob = createJobStubState('attendancesCancel', /\/api\/jobs\/attendancesCancel.*/)
  const confirmationJob = createJobStubState('attendancesConfirmation', /\/api\/jobs\/attendancesConfirmation.*/)

  mockAdapter
    .onGet(/\/api\/attendances\/(\d+)$/).reply(config => {
      const m = config.url!.match(/\/(\d+)$/)
      const id = m && +m[1]
      return id ? [HttpStatusCode.OK, createAttendanceResponseStub(id)] : [HttpStatusCode.NotFound]
    })
    .onGet('/api/attendances').reply(config => [HttpStatusCode.OK, createAttendanceIndexResponseStub(config.params)])
    .onPost('/api/attendances').reply(HttpStatusCode.Created)
    .onPut(/\/api\/attendances\/\d+$/).reply(HttpStatusCode.NoContent)
    .onPost(/\/api\/attendances\/\d+\/cancel$/).reply(HttpStatusCode.NoContent)

  // 一括キャンセル時の処理
  // - 1度目はしばらく待った後に失敗する
  // - 2度目以降は常に成功となる
  mockAdapter.onPost('/api/attendances/cancel').reply(() => {
    updateJobStubState(cancelJob)
    return [HttpStatusCode.Created, createJobResponseStub(cancelJob.token, JobStatus.waiting)]
  })
  range(1, 2).forEach(() => mockAdapter.onGet(cancelJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(cancelJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(cancelJob.regex).replyOnce(() => {
    const content = createJobResponseStub(cancelJob.token, JobStatus.failure)
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(cancelJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(cancelJob.token, JobStatus.success)]
  })

  // 一括確定時の処理
  // - 1度目はしばらく待った後に失敗する
  // - 2度目以降は常に成功となる
  mockAdapter.onPost('/api/attendances/confirmation').reply(() => {
    updateJobStubState(confirmationJob)
    return [HttpStatusCode.Created, createJobResponseStub(confirmationJob.token, JobStatus.waiting)]
  })
  range(1, 2).forEach(() => mockAdapter.onGet(confirmationJob.regex).replyOnce(() => {
    return [HttpStatusCode.OK, createJobResponseStub(confirmationJob.token, JobStatus.inProgress)]
  }))
  mockAdapter.onGet(confirmationJob.regex).replyOnce(() => {
    const content = createJobResponseStub(confirmationJob.token, JobStatus.failure)
    return [HttpStatusCode.OK, content]
  })
  mockAdapter.onGet(confirmationJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(confirmationJob.token, JobStatus.success)]
  })
}
