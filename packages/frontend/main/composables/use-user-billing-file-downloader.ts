/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { useAxios } from '~/composables/use-axios'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { Refs } from '~/support/reactive'

type FnName = keyof UserBillingsApi.Download
type DownloadForm = UserBillingsApi.DownloadForm
type Download = (
  fileName: string,
  fnName: FnName,
  form: DownloadForm
) => Promise<void>
type ExportFunctions = {
  [K in FnName]: (form: Parameters<UserBillingsApi.Download[K]>[0]['form']) => ReturnType<Download>
}
type ExportStatus = Refs<{
  errors: Record<string, string[]>
  progress: boolean
}>
type Args = {
  linkTo?: string
  started?: () => void
}

export const useUserBillingFileDownloader = (args: Args = {}): ExportFunctions & ExportStatus => {
  const { $api, $download, $form } = usePlugins()
  const { errors, progress, withAxios } = useAxios()
  const { execute } = useJobWithNotification()
  const download: Download = (fileName, fnName, form) => {
    // TODO useAxios 側に clearErrors みたいなのを追加した方が適切な気がするので相談する
    errors.value = {}
    return withAxios(() => {
      return $form.submit(() => execute({
        notificationProps: {
          linkToOnFailure: args.linkTo,
          text: {
            progress: `${fileName}のダウンロードを準備中です...`,
            success: `${fileName}のダウンロードを開始します`,
            failure: `${fileName}のダウンロードに失敗しました`
          }
        },
        process: () => $api.userBillings[fnName]({ form } as UserBillingsApi.DownloadParams),
        started: args.started,
        success: job => {
          $download.uri(job.data.uri, job.data.filename)
        }
      }))
    })
  }

  return {
    downloadInvoices: (form: DownloadForm) => download('請求書', 'downloadInvoices', form),
    downloadNotices: (form: DownloadForm) => download('代理受領額通知書', 'downloadNotices', form),
    downloadReceipts: (form: DownloadForm) => download('領収書', 'downloadReceipts', form),
    downloadStatements: (form: DownloadForm) => download('介護サービス利用明細書', 'downloadStatements', form),
    errors,
    progress
  }
}
