/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { notificationStoreKey, ZNotification } from '~/composables/stores/use-notification-store'
import { useInjected } from '~/composables/use-injected'
import { useJobPolling } from '~/composables/use-job-polling'
import { useNotificationApi } from '~/composables/use-notification-api'
import { Job } from '~/models/job'

export type NotificationProps = {
  featureName?: string
  linkToOnFailure?: string | (() => string)
  linkToOnSuccess?: string | (() => string)
  text?: {
    progress?: string
    success?: string
    failure?: string
  }
}

export type ExecuteParams = {
  notificationProps: NotificationProps
  process: () => Promise<{ job: Job }>
  started?: () => void
  success?: (job: Job) => void
  failure?: (errors: string[], job: Job) => void
}

export type Execute = (params: ExecuteParams) => Promise<void>

export type UseJobWithNotification = {
  execute: Execute
}

export function useJobWithNotification (): UseJobWithNotification {
  let id: Job['token']
  const { startJobPolling } = useJobPolling()
  const { sendNotification } = useNotificationApi()
  const notificationStore = useInjected(notificationStoreKey)
  const update = (notification: Omit<ZNotification, 'id'>) => {
    notificationStore.updateNotification({
      ...notification,
      id
    })
    sendNotification({ body: notification.text })
  }

  return {
    execute: async ({ notificationProps, process, started, success, failure }) => {
      const { featureName, linkToOnFailure, linkToOnSuccess, text } = notificationProps
      const result = await startJobPolling(async () => {
        const res = await process()
        id = res.job.token
        notificationStore.addNotification({
          id,
          status: JobStatus.inProgress,
          featureName,
          text: text?.progress
        })
        notificationStore.updateIsDisplayed(true)
        started && started()
        return res
      })
      if (result === false) {
        update({
          linkToOnFailure: typeof linkToOnFailure === 'function' ? linkToOnFailure() : linkToOnFailure,
          status: JobStatus.failure,
          text: text?.failure
        })
      } else {
        const { job } = result
        if (job.status === JobStatus.failure) {
          const { errors, error } = job.data ?? { errors: undefined }
          // TODO: バックエンドが修正されたら、errorを削除する
          failure && failure(errors ?? error ?? [], job)
          update({
            linkToOnFailure: typeof linkToOnFailure === 'function' ? linkToOnFailure() : linkToOnFailure,
            status: JobStatus.failure,
            text: text?.failure
          })
        } else if (job.status === JobStatus.success) {
          success && success(job)
          update({
            linkToOnSuccess: typeof linkToOnSuccess === 'function' ? linkToOnSuccess() : linkToOnSuccess,
            status: JobStatus.success,
            text: text?.success
          })
        }
      }
      // 処理終了後は結果に関係なく表示する
      notificationStore.updateIsDisplayed(true)
    }
  }
}
