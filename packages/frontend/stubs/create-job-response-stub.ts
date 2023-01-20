/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { Job } from '~/models/job'
import { JobsApi } from '~/services/api/jobs-api'
import { createJobStub } from '~~/stubs/create-job-stub'

export const createJobResponseStub = (token: Job['token'], status: JobStatus): JobsApi.GetResponse => ({
  job: createJobStub(token, status)
})
