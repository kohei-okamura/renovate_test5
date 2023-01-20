/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { noop } from 'lodash'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { useUserBillingFileDownloader } from '~/composables/use-user-billing-file-downloader'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { DownloadService } from '~/services/download-service'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createUserBillingStubs } from '~~/stubs/create-user-billing-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { TEST_NOW } from '~~/test/helpers/date'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-job-polling')
jest.mock('~/composables/use-job-with-notification')
jest.mock('~/composables/use-plugins')

describe('composables/use-user-billing-file-downloader', () => {
  const { objectContaining } = expect
  const $api = createMockedApi('userBillings', 'withdrawalTransactions')
  const $download = createMock<DownloadService>()
  const $form = createMockedFormService()
  const userBillings = createUserBillingStubs(10)
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()
  const plugins = createMockedPlugins({
    $api,
    $download,
    $form
  })

  let hook: ReturnType<typeof useUserBillingFileDownloader>

  const token = '10'
  const ids = userBillings.map(x => x.id)
  const issuedOn = TEST_NOW

  beforeAll(() => {
    setupComposableTest()
    mocked(useJobPolling).mockReturnValue({
      cancelJobPolling,
      startJobPolling
    })
    mocked(useJobWithNotification).mockReturnValue({ execute })
    mocked(usePlugins).mockReturnValue(plugins)
    jest.spyOn($download, 'uri').mockResolvedValue()
    mocked(startJobPolling).mockImplementation(async init => await init())
    hook = useUserBillingFileDownloader()
  })

  afterAll(() => {
    mocked(startJobPolling).mockRestore()
    mocked($download.uri).mockRestore()
    mocked(usePlugins).mockRestore()
    mocked(useJobPolling).mockRestore()
  })

  afterEach(() => {
    execute.mockClear()
    mocked(startJobPolling).mockClear()
    mocked($download.uri).mockClear()
  })

  type Extended = Extract<keyof UserBillingsApi.Download, 'downloadInvoices' | 'downloadReceipts' | 'downloadNotices'>
  describe.each<string, string, Extended>([
    ['invoices', '請求書', 'downloadInvoices'],
    ['receipts', '領収書', 'downloadReceipts'],
    ['notices', '代理受領額通知書', 'downloadNotices']
  ])('download %s', (_, fileName, fnName) => {
    const form = { ids, issuedOn: TEST_NOW.toFormat(ISO_MONTH_FORMAT) }

    beforeAll(async () => {
      const job = createJobStub(token, JobStatus.waiting)
      await jest.spyOn($api.userBillings, fnName).mockResolvedValue({ job })
    })

    afterAll(() => {
      mocked($api.userBillings[fnName]).mockRestore()
    })

    afterEach(() => {
      mocked($api.userBillings[fnName]).mockClear()
    })

    it('should call useJobWithNotification.execute', async () => {
      await hook[fnName](form)

      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(objectContaining({
        notificationProps: objectContaining({
          text: objectContaining({
            progress: `${fileName}のダウンロードを準備中です...`,
            success: `${fileName}のダウンロードを開始します`,
            failure: `${fileName}のダウンロードに失敗しました`
          })
        }),
        process: expect.any(Function),
        success: expect.any(Function)
      }))
    })

    it(`should call $api.userBillings.${fnName} when positive clicked`, async () => {
      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })

      await hook[fnName](form)

      expect($api.userBillings[fnName]).toHaveBeenCalledTimes(1)
      expect($api.userBillings[fnName]).toHaveBeenCalledWith({ form })

      mocked(execute).mockReset()
    })

    it('should start downloading when process completed successfully', async () => {
      const job = createJobStub(token, JobStatus.success)
      mocked(execute).mockImplementation(async ({ success }) => {
        await (success ?? noop)(job)
      })

      await hook[fnName](form)

      expect($download.uri).toHaveBeenCalledTimes(1)
      expect($download.uri).toHaveBeenCalledWith(job.data.uri, job.data.filename)
      mocked(execute).mockReset()
    })
  })

  describe.each<string, string, Exclude<keyof UserBillingsApi.Download, Extended>>([
    ['statements', '介護サービス利用明細書', 'downloadStatements']
  ])('download %s', (_, fileName, fnName) => {
    const form = { ids, issuedOn }

    beforeAll(async () => {
      const job = createJobStub(token, JobStatus.waiting)
      await jest.spyOn($api.userBillings, fnName).mockResolvedValue({ job })
    })

    afterAll(() => {
      mocked($api.userBillings[fnName]).mockRestore()
    })

    afterEach(() => {
      mocked($api.userBillings[fnName]).mockClear()
    })

    it('should call useJobWithNotification.execute', async () => {
      await hook[fnName](form)

      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(objectContaining({
        notificationProps: objectContaining({
          text: objectContaining({
            progress: `${fileName}のダウンロードを準備中です...`,
            success: `${fileName}のダウンロードを開始します`,
            failure: `${fileName}のダウンロードに失敗しました`
          })
        }),
        process: expect.any(Function),
        success: expect.any(Function)
      }))
    })

    it(`should call $api.userBillings.${fnName} when positive clicked`, async () => {
      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })

      await hook[fnName](form)

      expect($api.userBillings[fnName]).toHaveBeenCalledTimes(1)
      expect($api.userBillings[fnName]).toHaveBeenCalledWith({ form })

      mocked(execute).mockReset()
    })

    it('should start downloading when process completed successfully', async () => {
      const job = createJobStub(token, JobStatus.success)
      mocked(execute).mockImplementation(async ({ success }) => {
        await (success ?? noop)(job)
      })

      await hook[fnName](form)

      expect($download.uri).toHaveBeenCalledTimes(1)
      expect($download.uri).toHaveBeenCalledWith(job.data.uri, job.data.filename)
      mocked(execute).mockReset()
    })
  })
})
