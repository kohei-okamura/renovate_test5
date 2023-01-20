/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { usePlugins } from '~/composables/use-plugins'
import { CancelPolling, usePolling } from '~/composables/use-polling'
import { Job } from '~/models/job'

type JobContainer = {
  job: Job
}

export type CancelJobPolling = CancelPolling

export type StartJobPolling = (init: () => JobContainer | Promise<JobContainer>) => Promise<JobContainer | false>

type JobPollingFunctions = {
  cancelJobPolling: CancelJobPolling
  startJobPolling: StartJobPolling
}

const intervalMilliseconds = (n: number): number => {
  if (n < 10) { // 〜30秒
    return 3000
  } else if (n < 16) { // 〜60秒
    return 5000
  } else if (n < 28) { // 〜180秒
    return 10000
  } else {
    return 30000
  }
}

export function useJobPolling (): JobPollingFunctions {
  const { $api } = usePlugins()
  const { cancelPolling, startPolling } = usePolling()
  const cancelJobPolling = cancelPolling
  const startJobPolling: StartJobPolling = init => startPolling({
    init,
    test: ({ job }) => job.status === JobStatus.success || job.status === JobStatus.failure,
    poll: ({ job }) => $api.jobs.get({ token: job.token }),
    intervalMilliseconds
  })
  return {
    cancelJobPolling,
    startJobPolling
  }
}
