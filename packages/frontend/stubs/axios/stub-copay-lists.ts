/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { HttpStatusCode } from '~/models/http-status-code'
import { createJobStubState, StubFunction, updateJobStubState } from '~~/stubs/axios/utils'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'

/**
 * 利用者負担額一覧表 API をスタブ化する.
 */
export const stubCopayLists: StubFunction = mockAdapter => {
  // 利用者負担額一覧表ダウンロード
  const downloadJob = createJobStubState('downloadJob', /\/api\/jobs\/downloadJob.*/)
  mockAdapter.onPost(/\/api\/dws-billings\/\d+\/copay-lists$/).reply(() => {
    updateJobStubState(downloadJob)
    return [HttpStatusCode.Created, createJobResponseStub(downloadJob.token, JobStatus.waiting)]
  })
  mockAdapter.onGet(downloadJob.regex).reply(() => {
    return [HttpStatusCode.OK, createJobResponseStub(downloadJob.token, JobStatus.success)]
  })
}
