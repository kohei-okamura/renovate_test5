/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { isEmpty } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { noop } from 'lodash'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import { useUsers } from '~/composables/use-users'
import { Auth } from '~/models/auth'
import { ISO_DATE_FORMAT } from '~/models/date'
import WithdrawalTransactionsIndexPage from '~/pages/withdrawal-transactions/index.vue'
import { DownloadService } from '~/services/download-service'
import { RouteQuery } from '~/support/router/types'
import { mapValues } from '~/support/utils/map-values'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { createWithdrawalTransactionStubs } from '~~/stubs/create-withdrawal-transaction-stub'
import { createWithdrawalTransactionsStoreStub } from '~~/stubs/create-withdrawal-transactions-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-job-polling')
jest.mock('~/composables/use-job-with-notification')
jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-users')

describe('pages/withdrawal-transactions/index.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const { objectContaining } = expect
  const $api = createMockedApi('withdrawalTransactions')
  const $download = createMock<DownloadService>()
  const $form = createMockedFormService()
  const withdrawalTransactions = createWithdrawalTransactionStubs(10)
  const withdrawalTransactionsStore = createWithdrawalTransactionsStoreStub({ withdrawalTransactions })
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const testDate = TEST_NOW
  const startDate = testDate.toFormat(ISO_DATE_FORMAT)
  const endDate = testDate.plus({ months: 2 }).toFormat(ISO_DATE_FORMAT)
  const initParams: Record<string, unknown> = {
    start: undefined,
    end: undefined,
    itemsPerPage: 10
  }
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = MountOptions<Vue> & {
    auth?: Partial<Auth>
    isShallow?: true
    query?: RouteQuery
  }

  function mountComponent ({ auth, isShallow, query, ...options }: MountComponentParams = {}) {
    mocked(useJobPolling).mockReturnValue({
      cancelJobPolling,
      startJobPolling
    })
    const fn = isShallow ? shallowMount : mount
    const $routes = createMockedRoutes({ query: query ?? {} })
    wrapper = fn(WithdrawalTransactionsIndexPage, {
      ...provides([sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]),
      ...options,
      mocks: {
        $api,
        $download,
        $form,
        $routes,
        ...options?.mocks
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useJobWithNotification).mockReturnValue({ execute })
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useUsers).mockReturnValue(createUseUsersStub())
  })

  afterAll(() => {
    mocked(useUsers).mockReset()
    mocked(useOffices).mockReset()
    mocked(useJobPolling).mockReset()
  })

  beforeEach(() => {
    mocked(withdrawalTransactionsStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should not call withdrawalTransactionsStore.getIndex it there is no query', () => {
    mountComponent({ isShallow: true })
    expect(withdrawalTransactionsStore.getIndex).not.toHaveBeenCalled()
    unmountComponent()
  })

  it.each<Record<string, unknown>>([
    [{ itemsPerPage: 10 }],
    [{ itemsPerPage: 10, start: startDate, end: endDate }]
  ])('should call withdrawalTransactionsStore.getIndex correct query with %s', (params, expected = params) => {
    const query = mapValues(params, x => isEmpty(x) ? '' : String(x))
    mountComponent({ isShallow: true, query })

    expect(withdrawalTransactionsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(withdrawalTransactionsStore.getIndex).toHaveBeenCalledWith(createFormData({ ...initParams, ...expected }))

    unmountComponent()
  })

  describe('download', () => {
    const token = '10'
    const item = withdrawalTransactions[0]

    beforeAll(() => {
      const job = createJobStub(token, JobStatus.waiting)
      mocked(startJobPolling).mockImplementation(async init => await init())
      jest.spyOn($api.withdrawalTransactions, 'download').mockResolvedValue({ job })
      jest.spyOn($download, 'uri').mockResolvedValue()
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
      mocked($download.uri).mockRestore()
      mocked($api.withdrawalTransactions.download).mockRestore()
      mocked(startJobPolling).mockRestore()
    })

    afterEach(() => {
      mocked($download.uri).mockReset()
      mocked($api.withdrawalTransactions.download).mockClear()
      mocked(startJobPolling).mockClear()
    })

    it('should call useJobWithNotification.execute', async () => {
      await wrapper.find('[data-table]').vm.$emit('click:row', item)

      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(objectContaining({
        notificationProps: objectContaining({
          text: objectContaining({
            progress: '全銀ファイルのダウンロードを準備中です...',
            success: '全銀ファイルのダウンロードを開始します',
            failure: '全銀ファイルのダウンロードに失敗しました'
          })
        }),
        process: expect.any(Function),
        success: expect.any(Function)
      }))
    })

    it('should call $api.withdrawalTransactions.download', async () => {
      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })

      await wrapper.find('[data-table]').vm.$emit('click:row', item)

      expect($api.withdrawalTransactions.download).toHaveBeenCalledTimes(1)
      expect($api.withdrawalTransactions.download).toHaveBeenCalledWith({ form: { id: item.id } })
      mocked(execute).mockReset()
    })

    it('should start downloading when process completed successfully', async () => {
      const job = createJobStub(token, JobStatus.success)
      mocked(execute).mockImplementation(async ({ success }) => {
        await (success ?? noop)(job)
      })

      await wrapper.find('[data-table]').vm.$emit('click:row', item)

      expect($download.uri).toHaveBeenCalledTimes(1)
      expect($download.uri).toHaveBeenCalledWith(job.data.uri, job.data.filename)
      mocked(execute).mockReset()
    })
  })
})
