/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { mocked } from '@zinger/helpers/testing/mocked'
import { useJobPolling } from '~/composables/use-job-polling'
import { usePlugins } from '~/composables/use-plugins'
import { usePolling } from '~/composables/use-polling'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')
jest.mock('~/composables/use-polling')

describe('composables/use-job-polling', () => {
  const $api = createMockedApi('jobs')
  const plugins = createMockedPlugins({ $api })
  const startPolling = jest.fn()
  const cancelPolling = jest.fn()

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
    mocked(usePolling).mockReturnValue({ startPolling, cancelPolling })
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
    mocked(usePolling).mockReset()
  })

  afterEach(() => {
    startPolling.mockReset()
    cancelPolling.mockReset()
  })

  describe('startJobPolling', () => {
    const { objectContaining } = expect
    const job = createJobStub('1', JobStatus.waiting)
    const init = () => Promise.resolve({ job })

    it('should call startPolling', async () => {
      const { startJobPolling } = useJobPolling()

      await startJobPolling(init)

      expect(startPolling).toHaveBeenCalledTimes(1)
      expect(startPolling).toHaveBeenCalledWith(objectContaining({ init }))
    })

    it('should continue polling while job.status === JobStatus.waiting || job.status === JobStatus.inProgress', async () => {
      const { startJobPolling } = useJobPolling()
      await startJobPolling(init)
      const { test } = startPolling.mock.calls[0][0]

      expect(test({ job: createJobStub('1', JobStatus.waiting) })).toBeFalse()
      expect(test({ job: createJobStub('1', JobStatus.inProgress) })).toBeFalse()
      expect(test({ job: createJobStub('1', JobStatus.success) })).toBeTrue()
      expect(test({ job: createJobStub('1', JobStatus.failure) })).toBeTrue()
    })

    it('should call $api.jobs.get when polling', async () => {
      const { startJobPolling } = useJobPolling()
      await startJobPolling(init)
      const { poll } = startPolling.mock.calls[0][0]
      jest.spyOn($api.jobs, 'get').mockResolvedValue({ job })

      await poll({ job })

      expect($api.jobs.get).toHaveBeenCalledTimes(1)
      expect($api.jobs.get).toHaveBeenCalledWith({ token: job.token })
    })

    it.each([
      [3000, 1],
      [3000, 10],
      [5000, 11],
      [5000, 16],
      [10000, 17],
      [10000, 28],
      [30000, 29],
      [30000, 9999]
    ])('should wait %n ms before %nth retry', async (ms, retry) => {
      const { startJobPolling } = useJobPolling()
      await startJobPolling(init)
      const { intervalMilliseconds } = startPolling.mock.calls[0][0]

      const x = intervalMilliseconds(retry - 1)

      expect(x).toBe(ms)
    })
  })

  describe('cancelJobPolling', () => {
    it('should be cancelPolling', () => {
      const { cancelJobPolling } = useJobPolling()
      expect(cancelJobPolling).toBe(cancelPolling)
    })
  })
})
