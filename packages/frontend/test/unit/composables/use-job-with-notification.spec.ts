/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { noop } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { NotificationStore, useNotificationStore } from '~/composables/stores/use-notification-store'
import { useInjected } from '~/composables/use-injected'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { NotificationProps, useJobWithNotification } from '~/composables/use-job-with-notification'
import { useNotificationApi } from '~/composables/use-notification-api'
import { Job } from '~/models/job'
import { createJobStub } from '~~/stubs/create-job-stub'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-job-polling')
jest.mock('~/composables/use-injected')
jest.mock('~/composables/use-notification-api')

describe('composables/use-job-with-notification', () => {
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const notificationProps: NotificationProps = {
    featureName: 'test_use-job-with-notification',
    linkToOnFailure: '/home',
    linkToOnSuccess: '/dashboard',
    text: {
      progress: 'This task is in progress.',
      success: 'This task has completed successfully.',
      failure: 'This task failed and ended.'
    }
  }
  let notificationStore: NotificationStore

  beforeAll(() => {
    setupComposableTest()
    mocked(useJobPolling).mockReturnValue({
      cancelJobPolling,
      startJobPolling
    })
    mocked(useNotificationApi).mockReturnValue({
      askPermission: () => Promise.resolve(),
      closeNotification: noop,
      isAlreadyConfirmed: computed(() => true),
      isDenied: computed(() => false),
      isGranted: computed(() => true),
      statusText: computed(() => 'デスクトップ通知は有効です'),
      sendNotification: noop
    })
    notificationStore = useNotificationStore()
    mocked(useInjected).mockReturnValue(notificationStore)
    mocked(startJobPolling).mockImplementation(async init => await init())
  })

  afterAll(() => {
    mocked(useJobPolling).mockReset()
    mocked(useNotificationApi).mockReset()
    mocked(useInjected).mockReset()
    mocked(startJobPolling).mockReset()
  })

  afterEach(() => {
    notificationStore.resetState()
  })

  it('should add notification when the job starts', async () => {
    jest.spyOn(notificationStore, 'addNotification')
    const mockFn = jest.fn().mockImplementation(() => noop())

    const { execute } = useJobWithNotification()
    const token = '10'
    const job = createJobStub(token, JobStatus.waiting)

    await execute({
      notificationProps,
      process: () => Promise.resolve({ job }),
      started: mockFn
    })

    expect(notificationStore.addNotification).toHaveBeenCalledTimes(1)
    expect(notificationStore.addNotification).toHaveBeenCalledWith({
      id: token,
      status: JobStatus.inProgress,
      featureName: notificationProps.featureName,
      text: notificationProps.text!.progress
    })

    mocked(notificationStore.addNotification).mockReset()
    expect(mockFn).toHaveBeenCalledTimes(1)
  })

  it.each([
    ['/dashboard1', '/dashboard1'],
    ['/dashboard2', () => '/dashboard2']
  ])(
    'should call success() when the job has ended with success',
    async (expectedLink, linkToOnSuccess) => {
      jest.spyOn(notificationStore, 'updateNotification')
      const mockFn = jest.fn().mockImplementation((_job: Job) => noop())

      const { execute } = useJobWithNotification()
      const job = createJobStub('10', JobStatus.success)

      await execute({
        notificationProps: { ...notificationProps, linkToOnSuccess },
        process: () => Promise.resolve({ job }),
        success: mockFn
      })

      expect(notificationStore.updateNotification).toHaveBeenCalledTimes(1)
      expect(notificationStore.updateNotification).toBeCalledWith({
        id: job.token,
        linkToOnSuccess: expectedLink,
        status: JobStatus.success,
        text: notificationProps.text!.success
      })
      expect(mockFn).toHaveBeenCalledTimes(1)
      expect(mockFn).toHaveBeenCalledWith(job)

      mocked(notificationStore.updateNotification).mockRestore()
    }
  )

  it.each([
    ['/home1', '/home1'],
    ['/home2', () => '/home2']
  ])(
    'should call failure() when the job has ended with failure',
    async (expectedLink, linkToOnFailure) => {
      jest.spyOn(notificationStore, 'updateNotification')
      const mockFn = jest.fn().mockImplementation((_errors: string[], _job: Job) => noop())

      const { execute } = useJobWithNotification()
      const job = createJobStub('10', JobStatus.failure)
      const errors = [...Array(30)].map((_, i) => `何かが足りない気がする（行番号${i}）。`)
      Object.assign(job.data, { errors })

      await execute({
        notificationProps: { ...notificationProps, linkToOnFailure },
        process: () => Promise.resolve({ job }),
        failure: mockFn
      })

      expect(notificationStore.updateNotification).toHaveBeenCalledTimes(1)
      expect(notificationStore.updateNotification).toBeCalledWith({
        id: job.token,
        linkToOnFailure: expectedLink,
        status: JobStatus.failure,
        text: notificationProps.text!.failure
      })
      expect(mockFn).toHaveBeenCalledTimes(1)
      expect(mockFn).toHaveBeenCalledWith(errors, job)

      mocked(notificationStore.updateNotification).mockRestore()
    }
  )
})
