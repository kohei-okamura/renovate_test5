/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { Permission } from '@zinger/enums/lib/permission'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { UserBillingUsedService } from '@zinger/enums/lib/user-billing-used-service'
import { isEmpty } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { colors } from '~/colors'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userBillingsStoreKey } from '~/composables/stores/use-user-billings-store'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import { useUserBillingFileDownloader } from '~/composables/use-user-billing-file-downloader'
import { useUsers } from '~/composables/use-users'
import { Auth } from '~/models/auth'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { HttpStatusCode } from '~/models/http-status-code'
import { VSelectOption } from '~/models/vuetify'
import UserBillingsIndexPage from '~/pages/user-billings/index.vue'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { DownloadService } from '~/services/download-service'
import { SnackbarService } from '~/services/snackbar-service'
import { RouteQuery } from '~/support/router/types'
import { mapValues } from '~/support/utils/map-values'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { createUserBillingStubs } from '~~/stubs/create-user-billing-stub'
import { createUserBillingsStoreStub } from '~~/stubs/create-user-billings-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-job-polling')
jest.mock('~/composables/use-job-with-notification')
jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-users')
jest.mock('~/composables/use-user-billing-file-downloader')

describe('pages/user-billings/index.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const { objectContaining } = expect
  const $api = createMockedApi('userBillings', 'withdrawalTransactions')
  const $download = createMock<DownloadService>()
  const $confirm = createMock<ConfirmDialogService>()
  const $form = createMockedFormService()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const userBillings = createUserBillingStubs(10)
  const userBillingsForDownloadDialog = userBillings.filter(x => x.result !== UserBillingResult.none)
  const userBillingsStore = createUserBillingsStoreStub({ userBillings })
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const testYearMonth = TEST_NOW.toFormat(ISO_MONTH_FORMAT)
  const initParams: Record<string, unknown> = {
    officeId: '',
    providedIn: undefined,
    issuedIn: undefined,
    paymentMethod: '',
    result: '',
    usedService: '',
    itemsPerPage: 100
  }
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()
  const downloader: ReturnType<typeof useUserBillingFileDownloader> = {
    downloadInvoices: jest.fn(),
    downloadNotices: jest.fn(),
    downloadReceipts: jest.fn(),
    downloadStatements: jest.fn(),
    errors: ref({}),
    progress: ref(false)
  }

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
    wrapper = fn(UserBillingsIndexPage, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })],
        [userBillingsStoreKey, userBillingsStore]
      ),
      ...options,
      mocks: {
        $api,
        $download,
        $confirm,
        $form,
        $router,
        $routes,
        $snackbar,
        ...options?.mocks
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useUserBillingFileDownloader).mockReturnValue(downloader)
    mocked(useJobWithNotification).mockReturnValue({ execute })
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useUsers).mockReturnValue(createUseUsersStub())
  })

  afterAll(() => {
    mocked(useUserBillingFileDownloader).mockRestore()
    mocked(useUsers).mockReset()
    mocked(useOffices).mockReset()
    mocked(useJobPolling).mockReset()
  })

  beforeEach(() => {
    mocked(userBillingsStore.getIndex).mockClear()
  })

  afterEach(() => {
    execute.mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should not call userBillingsStore.getIndex it there is no query', () => {
    mountComponent({ isShallow: true })
    expect(userBillingsStore.getIndex).not.toHaveBeenCalled()
    unmountComponent()
  })

  it.each<Record<string, unknown>>([
    [{ itemsPerPage: 100 }],
    [{ itemsPerPage: 100, officeId: 1, providedIn: testYearMonth, issuedIn: testYearMonth }],
    [{ itemsPerPage: 100, officeId: 2, paymentMethod: PaymentMethod.transfer, result: UserBillingResult.paid }],
    [{ itemsPerPage: 100, officeId: 2, usedService: UserBillingUsedService.disabilitiesWelfareService }]
  ])('should call userBillingsStore.getIndex correct query with %s', (params, expected = params) => {
    const query = mapValues(params, x => isEmpty(x) ? '' : String(x))
    mountComponent({ isShallow: true, query })

    expect(userBillingsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(userBillingsStore.getIndex).toHaveBeenCalledWith(createFormData({ ...initParams, ...expected }))

    unmountComponent()
  })

  describe('action button', () => {
    let checkboxWrapper: Wrapper<any>

    beforeAll(() => {
      mountComponent({
        stubs: [
          'v-row',
          'v-col',
          'z-select-search-condition',
          'z-keyword-filter-autocomplete',
          'z-select',
          'v-fade-transition',
          'z-date-confirm-dialog'
        ]
      })
      checkboxWrapper = wrapper.find('.v-data-table-header .v-data-table__checkbox')
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should not display when no item selected', () => {
      expect(wrapper.find('[data-action-button]')).not.toExist()
    })

    it('should display when item selected', async () => {
      await click(() => checkboxWrapper)
      expect(wrapper.find('[data-action-button]')).toExist()
      await click(() => checkboxWrapper)
    })

    it('should be disabled when no action selected', async () => {
      await click(() => checkboxWrapper)
      expect(wrapper.find('[data-action-button]')).toBeDisabled()
      await click(() => checkboxWrapper)
    })

    it('should not be disabled when action selected', async () => {
      await click(() => checkboxWrapper)
      await setData(wrapper, { action: 'register-deposit-date' })
      expect(wrapper.find('[data-action-button]')).not.toBeDisabled()
      await click(() => checkboxWrapper)
    })

    it('should call method "doAction" when clicked', async () => {
      jest.spyOn(wrapper.vm, 'doAction').mockImplementation()

      await click(() => checkboxWrapper)
      await setData(wrapper, { action: 'register-deposit-date' })
      await submit(() => wrapper.find('[data-action-form]'))
      expect(wrapper.vm.doAction).toHaveBeenCalledTimes(1)
      await click(() => checkboxWrapper)
      mocked(wrapper.vm.doAction).mockRestore()
    })
  })

  describe('doAction', () => {
    type AllDownloaderFunctions = Omit<ReturnType<typeof useUserBillingFileDownloader>, 'errors' | 'progress'>
    type withAlertDownloaderFunctions = Pick<AllDownloaderFunctions, 'downloadInvoices' | 'downloadReceipts'>
    type DownloaderFunctions = Omit<AllDownloaderFunctions, keyof withAlertDownloaderFunctions>

    const token = '10'
    const ids = userBillings.map(x => x.id)
    const idsForDownloadDialog = userBillingsForDownloadDialog.map(x => x.id)

    beforeAll(() => {
      const job = createJobStub(token, JobStatus.waiting)
      jest.spyOn($api.userBillings, 'depositCancellation').mockResolvedValue({ job })
      jest.spyOn($api.userBillings, 'depositRegistration').mockResolvedValue({ job })
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($download, 'uri').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mocked(startJobPolling).mockImplementation(async init => await init())
      mountComponent({ isShallow: true })
    })

    afterAll(() => {
      unmountComponent()
      mocked(startJobPolling).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($download.uri).mockRestore()
      mocked($confirm.show).mockRestore()
      mocked($api.userBillings.depositCancellation).mockRestore()
      mocked($api.userBillings.depositRegistration).mockRestore()
    })

    afterEach(() => {
      mocked(startJobPolling).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($download.uri).mockReset()
      mocked($confirm.show).mockClear()
      mocked($api.userBillings.depositCancellation).mockClear()
      mocked($api.userBillings.depositRegistration).mockClear()
    })

    describe('register deposit date', () => {
      beforeEach(() => {
        setData(wrapper, { action: 'register-deposit-date', selected: userBillings })
      })

      it('should show date confirmation dialog', async () => {
        const dialog = wrapper.find('[data-date-confirm-dialog="depositedOn"]')

        expect(dialog.props().active).toBeFalse()

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it('should call $api.userBillings.depositRegistration when positive clicked', async () => {
        const form = { ids, depositedOn: testYearMonth }

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.find('[data-date-confirm-dialog="depositedOn"]')

        await dialog.vm.$emit('click:positive', testYearMonth)

        expect($api.userBillings.depositRegistration).toHaveBeenCalledTimes(1)
        expect($api.userBillings.depositRegistration).toHaveBeenCalledWith({ form })
      })

      it('should not call $api.userBillings.depositRegistration when negative clicked', async () => {
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.find('[data-date-confirm-dialog="depositedOn"]')

        await dialog.vm.$emit('click:negative')

        expect($api.userBillings.depositRegistration).not.toHaveBeenCalled()
      })

      it('should display snackbar when registration was successful', async () => {
        const job = createJobStub(token, JobStatus.success)
        jest.spyOn($api.userBillings, 'depositRegistration').mockResolvedValueOnce({ job })

        await wrapper.vm.dateRegistrationDialog.run(testYearMonth)

        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('入金日を一括登録しました。')
      })

      it('should display snackbar when registration was failure', async () => {
        const job = createJobStub(token, JobStatus.failure)
        jest.spyOn($api.userBillings, 'depositRegistration').mockResolvedValueOnce({ job })

        await wrapper.vm.dateRegistrationDialog.run(testYearMonth)

        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('入金日の一括登録に失敗しました。')
      })
    })

    describe('delete deposit date', () => {
      beforeEach(() => {
        setData(wrapper, { action: 'delete-deposit-date', selected: userBillings })
      })

      it('should display confirmation dialog', async () => {
        mocked($confirm.show).mockResolvedValueOnce(false)

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          color: colors.critical,
          message: '選択した請求の入金日を削除します。\n\n本当によろしいですか？',
          positive: '削除'
        })
      })

      describe('not confirmed', () => {
        beforeEach(() => {
          mocked($confirm.show).mockResolvedValueOnce(false)
        })

        it('should not call any api when not confirmed', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($api.userBillings.depositCancellation).not.toHaveBeenCalled()
        })

        it('should not display snackbar when not confirmed', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.success).not.toHaveBeenCalled()
        })
      })
      describe('confirmed', () => {
        it('should call $api.userBillings.depositCancellation when the action is confirm', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($api.userBillings.depositCancellation).toHaveBeenCalledTimes(1)
          expect($api.userBillings.depositCancellation).toHaveBeenCalledWith({ form: { ids } })
        })

        it('should display snackbar when cancellation was successful', async () => {
          const job = createJobStub(token, JobStatus.success)
          jest.spyOn($api.userBillings, 'depositCancellation').mockResolvedValueOnce({ job })

          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.success).toHaveBeenCalledTimes(1)
          expect($snackbar.success).toHaveBeenCalledWith('入金日を一括削除しました。')
        })

        it('should display snackbar when cancellation was failure', async () => {
          const job = createJobStub(token, JobStatus.failure)
          jest.spyOn($api.userBillings, 'depositCancellation').mockResolvedValueOnce({ job })

          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.error).toHaveBeenCalledTimes(1)
          expect($snackbar.error).toHaveBeenCalledWith('入金日の一括削除に失敗しました。')
        })
      })
    })

    describe('create withdrawal transactions', () => {
      const userBilling = userBillings[0]

      beforeAll(() => {
        mountComponent()
        const job = createJobStub(token, JobStatus.waiting)
        jest.spyOn($api.withdrawalTransactions, 'create').mockResolvedValue({ job })
      })

      beforeEach(() => {
        setData(wrapper, {
          action: 'create-withdrawal-transactions',
          selected: [{
            ...userBilling,
            result: UserBillingResult.pending,
            user: { billingDestination: { paymentMethod: PaymentMethod.withdrawal } }
          }]
        })
      })

      it('should display alert when paymentMethod is not withdrawal', async () => {
        await setData(wrapper, {
          action: 'create-withdrawal-transactions',
          selected: [{
            ...userBilling,
            result: UserBillingResult.pending,
            user: { billingDestination: { paymentMethod: PaymentMethod.transfer } }
          }]
        })
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        expect(wrapper.find('[data-local-errors] .v-alert__content').text()).toContain('支払方法が「口座振替」の利用者のみを選択してください。')
      })

      it('should display alert when result is not pending', async () => {
        await setData(wrapper, {
          action: 'create-withdrawal-transactions',
          selected: [{
            ...userBilling,
            result: UserBillingResult.paid,
            user: { billingDestination: { paymentMethod: PaymentMethod.withdrawal } }
          }]
        })
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        expect(wrapper.find('[data-local-errors] .v-alert__content').text()).toContain('全銀ファイルの作成対象になったことのない利用者のみを選択してください。')
      })

      it('should display confirmation dialog', async () => {
        await setData(wrapper, {
          action: 'create-withdrawal-transactions',
          selected: [{
            ...userBilling,
            result: UserBillingResult.pending,
            user: { billingDestination: { paymentMethod: PaymentMethod.withdrawal } }
          }]
        })
        mocked($confirm.show).mockResolvedValueOnce(false)

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          color: colors.critical,
          message: '選択した請求の全銀ファイルを作成します。\n\n本当によろしいですか？',
          positive: '作成'
        })
      })

      describe('not confirmed', () => {
        beforeEach(() => {
          mocked($confirm.show).mockResolvedValueOnce(false)
        })

        it('should not call any api when not confirmed', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($api.withdrawalTransactions.create).not.toHaveBeenCalled()
        })

        it('should not display snackbar when not confirmed', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.success).not.toHaveBeenCalled()
        })
      })

      describe('confirmed', () => {
        it('should call useJobWithNotification.execute', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect(execute).toHaveBeenCalledTimes(1)
          expect(execute).toHaveBeenCalledWith(objectContaining({
            notificationProps: objectContaining({
              linkToOnFailure: '/user-billings',
              text: {
                progress: '全銀ファイルの作成を準備中です...',
                success: '全銀ファイルを作成しました',
                failure: '全銀ファイルの作成に失敗しました'
              }
            }),
            process: expect.any(Function),
            started: expect.any(Function),
            success: expect.any(Function)
          }))
        })

        it('should call $api.withdrawalTransactions.create when the action is confirm', async () => {
          mocked(execute).mockImplementation(async ({ process }) => {
            await process()
          })

          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($api.withdrawalTransactions.create).toHaveBeenCalledTimes(1)
          expect($api.withdrawalTransactions.create).toHaveBeenCalledWith({
            form: { userBillingIds: [userBilling.id] }
          })
          mocked(execute).mockReset()
        })
      })
    })

    describe.each<string, keyof withAlertDownloaderFunctions>([
      ['invoices', 'downloadInvoices'],
      ['receipts', 'downloadReceipts']
    ])('download %s', (type, fnName) => {
      const userBilling = userBillings[0]
      beforeAll(() => {
        mountComponent()
        const job = createJobStub(token, JobStatus.waiting)
        jest.spyOn($api.withdrawalTransactions, 'create').mockResolvedValue({ job })
      })

      afterEach(() => {
        mocked(useUserBillingFileDownloader).mockClear()
        mocked(downloader[fnName]).mockClear()
      })

      it('should show alert when userBillingResult is none', async () => {
        await setData(wrapper, {
          action: `download-${type}`,
          selected: [{
            ...userBilling,
            result: UserBillingResult.none
          }]
        })
        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        expect(dialog.props().active).toBeFalse()

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        expect(dialog.props().active).toBeFalse()
        const alertText = type === 'invoices'
          ? '請求結果が「請求なし」ではない利用者のみを選択してください。'
          : '請求結果が「入金済み」の利用者のみを選択してください。'
        expect(wrapper.find('[data-local-errors] .v-alert__content').text()).toContain(alertText)
      })

      it('should show date confirmation dialog', async () => {
        await setData(wrapper, {
          action: `download-${type}`,
          selected: [{
            ...userBilling,
            result: UserBillingResult.paid
          }]
        })
        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        expect(dialog.props().active).toBeFalse()

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it(`should call useUserBillingFileDownloader.${fnName} when positive clicked`, async () => {
        await setData(wrapper, { action: `download-${type}`, selected: userBillingsForDownloadDialog })
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const form = { ids: idsForDownloadDialog, issuedOn: testYearMonth }
        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        await dialog.vm.$emit('click:positive', testYearMonth)

        expect(downloader[fnName]).toHaveBeenCalledTimes(1)
        expect(downloader[fnName]).toHaveBeenCalledWith(form)
      })

      it(`should not call useUserBillingFileDownloader.${fnName} when negative clicked`, async () => {
        await setData(wrapper, { action: `download-${type}`, selected: userBillingsForDownloadDialog })
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        await dialog.vm.$emit('click:negative')

        expect(downloader[fnName]).not.toHaveBeenCalled()
      })
    })

    describe.each<string, keyof DownloaderFunctions>([
      ['notices', 'downloadNotices'],
      ['statements', 'downloadStatements']
    ])('download %s', (type, fnName) => {
      beforeAll(() => {
        mountComponent()
        const job = createJobStub(token, JobStatus.waiting)
        jest.spyOn($api.withdrawalTransactions, 'create').mockResolvedValue({ job })
      })

      afterEach(() => {
        mocked(useUserBillingFileDownloader).mockClear()
        mocked(downloader[fnName]).mockClear()
      })

      it(`should call useUserBillingFileDownloader.${fnName} when positive clicked`, async () => {
        await setData(wrapper, { action: `download-${type}`, selected: userBillingsForDownloadDialog })
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const form = { ids: idsForDownloadDialog, issuedOn: testYearMonth }
        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        await dialog.vm.$emit('click:positive', testYearMonth)

        expect(downloader[fnName]).toHaveBeenCalledTimes(1)
        expect(downloader[fnName]).toHaveBeenCalledWith(form)
      })

      it(`should not call useUserBillingFileDownloader.${fnName} when negative clicked`, async () => {
        await setData(wrapper, { action: `download-${type}`, selected: userBillingsForDownloadDialog })
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        await dialog.vm.$emit('click:negative')

        expect(downloader[fnName]).not.toHaveBeenCalled()
      })
    })

    describe('permissions', () => {
      it('should be rendered speed dial and all FABs when session auth is system admin', () => {
        mountComponent()
        expect(wrapper).toContainElement('[data-fab]')
        expect(wrapper).toContainElement('[data-withdrawal-transactions-download]')
        expect(wrapper).toContainElement('[data-withdrawal-transactions-upload]')
        unmountComponent()
      })

      it('should be rendered speed dial and all upload button when the staff has createWithdrawalTransactions', () => {
        const auth = {
          permissions: [Permission.createWithdrawalTransactions]
        }
        mountComponent({ auth })
        expect(wrapper).toContainElement('[data-fab]')
        expect(wrapper).not.toContainElement('[data-withdrawal-transactions-download]')
        expect(wrapper).toContainElement('[data-withdrawal-transactions-upload]')
        unmountComponent()
      })

      it('should be rendered speed dial and all download button when the staff has listWithdrawalTransactions', () => {
        const auth = {
          permissions: [Permission.listWithdrawalTransactions]
        }
        mountComponent({ auth })
        expect(wrapper).toContainElement('[data-fab]')
        expect(wrapper).toContainElement('[data-withdrawal-transactions-download]')
        expect(wrapper).not.toContainElement('[data-withdrawal-transactions-upload]')
        unmountComponent()
      })

      it('should not be rendered speed dial when the staff does not have permissions', () => {
        const auth = {
          permissions: []
        }
        mountComponent({ auth })
        expect(wrapper).not.toContainElement('[data-fab]')
        expect(wrapper).not.toContainElement('[data-withdrawal-transactions-download]')
        expect(wrapper).not.toContainElement('[data-withdrawal-transactions-upload]')
        unmountComponent()
      })
    })

    describe('api server response 400', () => {
      beforeAll(() => {
        mountComponent()
      })
      it('should display errors when server responses 400 Bad Request call $api.userBillings.depositRegistration', async () => {
        await setData(wrapper, { action: 'register-deposit-date', selected: userBillings })
        mocked($api.userBillings.depositRegistration)
          .mockReset()
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { ids: '不正なidが含まれています。', depositedOn: '入金日が不正です。' }
          }))
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()
        await wrapper.vm.dateRegistrationDialog.run(testYearMonth)

        const targetWrapper = wrapper.find('[data-action-errors] .v-alert__content')
        expect(targetWrapper.text()).toMatch(/(?=.*不正なidが含まれています。).*\n(?=.*入金日が不正です。).*/)
        expect(targetWrapper).toMatchSnapshot()
      })

      it('should display errors when server responses 400 Bad Request call $api.userBillings.depositRegistration', async () => {
        await setData(wrapper, { action: 'delete-deposit-date', selected: userBillings })
        mocked($api.userBillings.depositCancellation)
          .mockReset()
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { ids: '不正なidが含まれています。' }
          }))
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const targetWrapper = wrapper.find('[data-action-errors] .v-alert__content')
        expect(targetWrapper.text()).toContain('不正なidが含まれています。')
        expect(targetWrapper).toMatchSnapshot()
      })

      it('should display errors when server responses 400 Bad Request call $api.withdrawalTransactions.create', async () => {
        const job = createJobStub(token, JobStatus.waiting)
        jest.spyOn($api.withdrawalTransactions, 'create').mockResolvedValue({ job })
        await setData(wrapper, {
          action: 'create-withdrawal-transactions',
          selected: [{
            ...userBillings[0],
            result: UserBillingResult.pending,
            user: { billingDestination: { paymentMethod: PaymentMethod.withdrawal } }
          }]
        })
        mocked(execute).mockImplementation(async ({ process }) => {
          await process()
        })
        mocked($api.withdrawalTransactions.create)
          .mockReset()
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { userBillingIds: '不正なidが含まれています。' }
          }))
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const targetWrapper = wrapper.find('[data-action-errors] .v-alert__content')
        expect(targetWrapper.text()).toContain('不正なidが含まれています。')
        expect(targetWrapper).toMatchSnapshot()
        mocked(execute).mockReset()
      })

      describe.each<string, keyof AllDownloaderFunctions>([
        ['invoices', 'downloadInvoices'],
        ['receipts', 'downloadReceipts'],
        ['notices', 'downloadNotices'],
        ['statements', 'downloadStatements']
      ])('download %s', (type, fnName) => {
        beforeEach(() => {
          setData(wrapper, {
            action: `download-${type}`,
            selected: [{ ...userBillingsForDownloadDialog[0], result: UserBillingResult.paid }]
          })
        })

        afterEach(() => {
          mocked(useUserBillingFileDownloader).mockClear()
          mocked(downloader[fnName]).mockClear()
          downloader.errors.value = {}
        })

        it(`should display errors when error occurred in useUserBillingFileDownloader.${fnName}`, async () => {
          mocked(downloader[fnName]).mockImplementationOnce(() => {
            downloader.errors.value = { ids: ['不正なidが含まれています。'], issuedOn: ['発行日が不正です。'] }
            return Promise.resolve()
          })
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

          await dialog.vm.$emit('click:positive', testYearMonth)
          await wrapper.vm.$nextTick()

          const targetWrapper = wrapper.find('[data-action-errors] .v-alert__content')
          expect(targetWrapper.text()).toMatch(/(?=.*不正なidが含まれています。).*\n(?=.*発行日が不正です。).*/)
          expect(targetWrapper).toMatchSnapshot()
        })
      })
    })
  })

  describe('actions form select items', () => {
    const expectedText = {
      default: 'アクションを選択...',
      registerDepositDate: '入金日を登録する',
      deleteDepositDate: '入金日を削除する',
      withdrawalTransactions: '全銀ファイルを作成する',
      downloadInvoice: '請求書をダウンロードする',
      downloadReceipt: '領収書をダウンロードする',
      downloadProxyReceipt: '代理受領額通知書をダウンロードする',
      downloadLtcsUsageStatement: '介護サービス利用明細書をダウンロードする'
    }

    const getActionsTexts = () => wrapper.vm.actions.map((x: VSelectOption) => x.text)

    it('should be rendered all text when the staff has admin permission', () => {
      mountComponent({ isShallow: true })
      expect(getActionsTexts()).toEqual(Object.values(expectedText))
      unmountComponent()
    })

    it.each([
      [
        [
          expectedText.default,
          expectedText.registerDepositDate,
          expectedText.deleteDepositDate
        ],
        [Permission.updateUserBillings]
      ],
      [
        [
          expectedText.default,
          expectedText.withdrawalTransactions
        ],
        [Permission.createWithdrawalTransactions]
      ],
      [
        [
          expectedText.default,
          expectedText.downloadInvoice,
          expectedText.downloadReceipt,
          expectedText.downloadProxyReceipt,
          expectedText.downloadLtcsUsageStatement
        ],
        [Permission.viewUserBillings]
      ],
      [
        [expectedText.default],
        []
      ]
    ])('should be rendered %s when the staff has permission(s): %s', (texts, permissions) => {
      const auth = { permissions }
      mountComponent({ auth, isShallow: true })
      expect(getActionsTexts()).toEqual(texts)
      unmountComponent()
    })
  })
})
