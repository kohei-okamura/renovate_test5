/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { JobsApi } from '~/services/api/jobs-api'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/jobs-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let jobs: JobsApi.Definition

  beforeEach(() => {
    jobs = JobsApi.create(axios)
  })

  describe('get', () => {
    it('should get /api/jobs/:token', async () => {
      const token = '1'
      const status = JobStatus.inProgress
      const url = `/api/jobs/${token}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.Created, createJobStub(token, status))
      })

      await jobs.get({ token })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const token = '2'
      const status = JobStatus.success
      adapter.setup(x => {
        x.onGet(`/api/jobs/${token}`).replyOnce(HttpStatusCode.Created, createJobResponseStub(token, status))
      })

      const response = await jobs.get({ token })

      expect(response).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      const token = '3'
      adapter.setup(x => {
        x.onGet(`/api/jobs/${token}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = jobs.get({ token })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
