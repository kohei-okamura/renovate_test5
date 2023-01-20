/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { MountOptions, Wrapper } from '@vue/test-utils'
import { DwsBillingServiceReportFormat } from '@zinger/enums/lib/dws-billing-service-report-format'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import { noop } from 'lodash'
import Vue from 'vue'
import ZDwsBillingStatementListCard from '~/components/domain/billing/z-dws-billing-statement-list-card.vue'
import { DwsBillingStore, dwsBillingStoreKey, DwsBillingUnit } from '~/composables/stores/use-dws-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { DwsBillingBundle, DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingUser } from '~/models/dws-billing-user'
import { CopayListsApi } from '~/services/api/copay-lists-api'
import { DwsBillingServiceReportsApi } from '~/services/api/dws-billing-service-reports-api'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { DownloadService } from '~/services/download-service'
import { SnackbarService } from '~/services/snackbar-service'
import { toHiragana } from '~/support/jaco'
import { createDwsBillingBundleStub } from '~~/stubs/create-dws-billing-bundle-stub'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createDwsBillingStoreStub } from '~~/stubs/create-dws-billing-store-stub'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-job-polling')
jest.mock('~/composables/use-job-with-notification')

describe('z-dws-billing-statement-list-card.vue', () => {
  type CompareParameter = {
    dwsBillingBundleId: DwsBillingBundleId
    user: DwsBillingUser
  }

  /*
   * 各項目を DwsBillingUnit 単位にまとめる
   *
   * @param bundleId
   * @param data
   */
  const createBillingUnits = (
    bundleId: DwsBillingBundleId,
    cityName: DwsBillingBundle['cityName'],
    data: Pick<DwsBillingsApi.GetResponse, 'copayCoordinations' | 'reports' | 'statements'>
  ): DwsBillingUnit[] => {
    const { copayCoordinations, reports, statements } = data
    const isSame = (p1: CompareParameter, p2: CompareParameter) => {
      return p1.dwsBillingBundleId === p2.dwsBillingBundleId && p1.user.userId === p2.user.userId
    }
    return statements
      .filter(({ dwsBillingBundleId }) => bundleId === dwsBillingBundleId)
      .map((statement, index) => {
        return {
          id: statement.id,
          userName: statement.user.name.displayName,
          userPhoneticName: toHiragana(statement.user.name.phoneticDisplayName),
          dwsNumber: statement.user.dwsNumber,
          cityName,
          copayCoordination: copayCoordinations.find(v => isSame(v, statement)),
          homeHelpServiceReport: reports.find(
            v => v.format === DwsBillingServiceReportFormat.homeHelpService && isSame(v, statement)
          ),
          status: index % 2 === 0 ? DwsBillingStatus.ready : DwsBillingStatus.fixed,
          visitingCareForPwsdReport: reports.find(
            v => v.format === DwsBillingServiceReportFormat.visitingCareForPwsd && isSame(v, statement)
          ),
          statement
        }
      })
  }

  const { mount } = setupComponentTest()
  const $api = createMockedApi('dwsBillingStatements', 'dwsBillingServiceReports', 'copayLists')
  const $confirm = createMock<ConfirmDialogService>()
  const $download = createMock<DownloadService>()
  const $form = createMockedFormService()
  const $snackbar = createMock<SnackbarService>()
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const responseStub = createDwsBillingResponseStub()
  const bundle1 = createDwsBillingBundleStub({ providedIn: '2020-10', cityName: 'どこかの町' })
  const items: DwsBillingUnit[] = createBillingUnits(
    bundle1.id,
    bundle1.cityName,
    {
      copayCoordinations: responseStub.copayCoordinations,
      reports: responseStub.reports,
      statements: responseStub.statements
    }
  )
  const providedIn = '2020-10'
  const billingStatus = DwsBillingStatus.ready
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()
  const index = 1

  let wrapper: Wrapper<Vue & any>
  let store: DwsBillingStore

  function mountComponent (options: Partial<MountOptions<Vue>> = {}) {
    mocked(useJobPolling).mockReturnValue({
      cancelJobPolling,
      startJobPolling
    })
    mocked(useJobWithNotification).mockReturnValue({ execute })
    const billing = {
      ...responseStub.billing
    }
    const storeData = { billing }
    store = createDwsBillingStoreStub({
      ...responseStub,
      ...(storeData ?? {}),
      billing: {
        ...responseStub.billing,
        ...(storeData?.billing ?? {})
      }
    })
    wrapper = mount(ZDwsBillingStatementListCard, {
      ...options,
      propsData: {
        billingStatus,
        items,
        providedIn,
        index,
        ...options.propsData
      },
      ...provides(
        [dwsBillingStoreKey, store],
        [sessionStoreKey, createAuthStub({ isSystemAdmin: true })]
      ),
      mocks: {
        $api,
        $confirm,
        $download,
        $form,
        $snackbar
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    const propsData = {
      items,
      providedIn,
      index
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('filter', () => {
    beforeEach(() => {
      const units = items.map((x: DwsBillingUnit, index) => index % 2 === 0 ? x : ({ ...x, cityName: 'どこかの村' }))
      const propsData = {
        items: units,
        providedIn,
        index
      }
      mountComponent({ propsData })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should filter by userName', async () => {
      const keyword = '川'

      expect(wrapper.vm.units.every((x: DwsBillingUnit) => x.userName.includes(keyword))).toBeFalse()
      await setData(wrapper, { keyword })
      expect(wrapper.vm.units.every((x: DwsBillingUnit) => x.userName.includes(keyword))).toBeTrue()
    })

    it('should filter by userPhoneticName', async () => {
      const keyword = 'いし'

      expect(
        wrapper.vm.units.every((x: DwsBillingUnit) => x.userPhoneticName.includes(keyword))
      ).toBeFalse()
      await setData(wrapper, { keyword })
      expect(
        wrapper.vm.units.every((x: DwsBillingUnit) => x.userPhoneticName.includes(keyword))
      ).toBeTrue()
    })

    it('should filter by cityName', async () => {
      const keyword = '村'

      expect(wrapper.vm.units.every((x: DwsBillingUnit) => x.cityName.includes(keyword))).toBeFalse()
      await setData(wrapper, { keyword })
      expect(wrapper.vm.units.every((x: DwsBillingUnit) => x.cityName.includes(keyword))).toBeTrue()
    })

    it('should filter by status', async () => {
      const status = DwsBillingStatus.disabled

      expect(wrapper.vm.units.every((x: DwsBillingUnit) => x.statement!.status === status)).toBeFalse()
      await setData(wrapper, { status })
      expect(wrapper.vm.units.every((x: DwsBillingUnit) => x.statement!.status === status)).toBeTrue()
    })
  })

  describe('checkbox', () => {
    afterEach(() => {
      unmountComponent()
    })
    it('should display checkbox if billingStatus is not fixed', function () {
      const propsData = {
        billingStatus: DwsBillingStatus.ready
      }
      mountComponent({ propsData })
      expect(wrapper.find('.v-simple-checkbox')).toExist()
    })
    it('should not display checkbox if billingStatus is fixed', function () {
      const propsData = {
        billingStatus: DwsBillingStatus.fixed
      }
      mountComponent({ propsData })
      expect(wrapper.find('.v-simple-checkbox')).not.toExist()
    })
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
      await setData(wrapper, { action: 'confirm-statements' })
      expect(wrapper.find('[data-action-button]')).not.toBeDisabled()
      await click(() => checkboxWrapper)
    })

    it('should call the method "doAction" when clicked', async () => {
      jest.spyOn(wrapper.vm, 'doAction').mockImplementation()

      await click(() => checkboxWrapper)
      await setData(wrapper, { action: 'confirm-statements' })
      await submit(() => wrapper.find('[data-action-form]'))
      expect(wrapper.vm.doAction).toHaveBeenCalledTimes(1)
      await click(() => checkboxWrapper)
      mocked(wrapper.vm.doAction).mockRestore()
    })
  })

  describe('doAction', () => {
    const token = '10'

    beforeAll(() => {
      const job = createJobStub(token, JobStatus.waiting)
      jest.spyOn($api.dwsBillingStatements, 'bulkUpdateStatus').mockResolvedValue({ job })
      jest.spyOn($api.dwsBillingStatements, 'refresh').mockResolvedValue({ job })
      jest.spyOn($api.dwsBillingServiceReports, 'bulkUpdateStatus').mockResolvedValue({ job })
      jest.spyOn($api.copayLists, 'download').mockResolvedValue({ job })
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn($download, 'uri').mockResolvedValue()
      mocked(startJobPolling).mockImplementation(async init => await init())
      mocked(useJobWithNotification).mockReturnValue({ execute })
      mountComponent()
    })

    beforeEach(() => {
      jest.spyOn(store, 'get').mockResolvedValue()
    })

    afterAll(() => {
      unmountComponent()
      mocked(startJobPolling).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($download.uri).mockRestore()
      mocked($confirm.show).mockRestore()
      mocked($api.dwsBillingStatements.bulkUpdateStatus).mockRestore()
      mocked($api.dwsBillingStatements.refresh).mockRestore()
      mocked($api.dwsBillingServiceReports.bulkUpdateStatus).mockRestore()
      mocked($api.copayLists.download).mockRestore()
      mocked(store.get).mockRestore()
    })

    afterEach(() => {
      mocked(startJobPolling).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($download.uri).mockClear()
      mocked($confirm.show).mockClear()
      mocked($api.dwsBillingStatements.bulkUpdateStatus).mockClear()
      mocked($api.dwsBillingStatements.refresh).mockClear()
      mocked($api.dwsBillingServiceReports.bulkUpdateStatus).mockClear()
      mocked($api.copayLists.download).mockClear()
      mocked(store.get).mockClear()
      execute.mockClear()
    })

    describe('confirm statements', () => {
      beforeEach(async () => {
        await setData(wrapper, { action: 'confirm-statements', selected: items })
      })

      it('should call $api.dwsBillingStatements.bulkUpdateStatus when the positive button is clicked', async () => {
        const billingId = items[0].statement?.dwsBillingId
        const form: DwsBillingStatementsApi.BulkUpdateStatusForm = {
          ids: items.map(x => x.id),
          status: DwsBillingStatus.fixed
        }

        jest.spyOn($confirm, 'show').mockResolvedValueOnce(true)

        await wrapper.vm.doAction()

        expect($api.dwsBillingStatements.bulkUpdateStatus).toHaveBeenCalledTimes(1)
        expect($api.dwsBillingStatements.bulkUpdateStatus).toHaveBeenCalledWith({ billingId, form })
      })

      it('should not call $api.dwsBillingStatements.bulkUpdateStatus when the negative button is clicked', async () => {
        jest.spyOn($confirm, 'show').mockResolvedValueOnce(false)

        await wrapper.vm.doAction()

        expect($api.dwsBillingStatements.bulkUpdateStatus).not.toHaveBeenCalled()
      })

      it('should update dws billing store when the confirmation was succeeded', async () => {
        const billingId = items[0].statement?.dwsBillingId
        const job = createJobStub(token, JobStatus.success)

        jest.spyOn($api.dwsBillingStatements, 'bulkUpdateStatus').mockResolvedValueOnce({ job })

        await wrapper.vm.doAction()

        expect(store.get).toHaveBeenCalledTimes(1)
        expect(store.get).toHaveBeenCalledWith({ id: billingId })
      })
    })

    describe('confirm reports', () => {
      beforeEach(async () => {
        await setData(wrapper, { action: 'confirm-reports', selected: items })
      })

      it('should call $api.dwsBillingServiceReports.bulkUpdateStatus when the positive button is clicked', async () => {
        const billingId = items[0].statement?.dwsBillingId
        const form: DwsBillingServiceReportsApi.BulkUpdateStatusForm = {
          ids: items.flatMap(x => [x.homeHelpServiceReport, x.visitingCareForPwsdReport])
            .filter(Boolean)
            .map(x => x!.id),
          status: DwsBillingStatus.fixed
        }

        jest.spyOn($confirm, 'show').mockResolvedValueOnce(true)

        await wrapper.vm.doAction()

        expect($api.dwsBillingServiceReports.bulkUpdateStatus).toHaveBeenCalledTimes(1)
        expect($api.dwsBillingServiceReports.bulkUpdateStatus).toHaveBeenCalledWith({ billingId, form })
      })

      it('should not call $api.dwsBillingServiceReports.bulkUpdateStatus when the negative button is clicked', async () => {
        jest.spyOn($confirm, 'show').mockResolvedValueOnce(false)

        await wrapper.vm.doAction()

        expect($api.dwsBillingServiceReports.bulkUpdateStatus).not.toHaveBeenCalled()
      })

      it('should update dws billing store when the confirmation was succeeded', async () => {
        const billingId = items[0].statement?.dwsBillingId
        const job = createJobStub(token, JobStatus.success)

        jest.spyOn($api.dwsBillingServiceReports, 'bulkUpdateStatus').mockResolvedValueOnce({ job })

        await wrapper.vm.doAction()

        expect(store.get).toHaveBeenCalledTimes(1)
        expect(store.get).toHaveBeenCalledWith({ id: billingId })
      })
    })

    describe('refresh statement', () => {
      const billingId = items[0].statement?.dwsBillingId
      beforeEach(async () => {
        await setData(wrapper, { action: 'refresh-statements', selected: items })
      })

      it('should call $api.dwsBillingStatements.refresh when the positive button is clicked', async () => {
        mocked(execute).mockImplementation(async ({ process }) => {
          await process()
        })

        const form: DwsBillingStatementsApi.RefreshForm = {
          ids: items.map(x => x.id)
        }

        jest.spyOn($confirm, 'show').mockResolvedValueOnce(true)

        await wrapper.vm.doAction()

        expect($api.dwsBillingStatements.refresh).toHaveBeenCalledTimes(1)
        expect($api.dwsBillingStatements.refresh).toHaveBeenCalledWith({ billingId, form })
        mocked(execute).mockReset()
      })

      it('should not call $api.dwsBillingStatements.refresh when the negative button is clicked', async () => {
        jest.spyOn($confirm, 'show').mockResolvedValueOnce(false)

        await wrapper.vm.doAction()

        expect($api.dwsBillingStatements.refresh).not.toHaveBeenCalled()
      })

      it('should update dws billing store when the confirmation was succeeded', async () => {
        const job = createJobStub(token, JobStatus.success)
        mocked(execute).mockImplementation(async ({ success }) => {
          await (success ?? noop)(job)
        })

        jest.spyOn($api.dwsBillingStatements, 'refresh').mockResolvedValueOnce({ job })

        await wrapper.vm.doAction()

        expect(store.get).toHaveBeenCalledTimes(1)
        expect(store.get).toHaveBeenCalledWith({ id: billingId })

        mocked(execute).mockReset()
      })
    })

    describe('copay lists', () => {
      const billingId = items[0].statement?.dwsBillingId

      beforeEach(async () => {
        await setData(wrapper, { action: 'copay-lists', selected: items })
      })

      it('should not call $api.copayLists.download when a radio is not selected', async () => {
        await wrapper.vm.doAction()

        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })
        await dialog.vm.$emit('click:positive', new MouseEvent('click'))
        await flushPromises()

        expect($api.copayLists.download).not.toHaveBeenCalled()
      })

      it('should call $api.copayLists.download when a radio is selected and the positive button is clicked', async () => {
        mocked(execute).mockImplementationOnce(async ({ process }) => {
          await process()
        })
        const isDivided = true
        const form: CopayListsApi.DownloadForm = {
          ids: items.map(x => x.id),
          isDivided
        }

        await wrapper.vm.doAction()

        await setData(wrapper, {
          copayListDialog: {
            isDivided: ref(isDivided)
          }
        })

        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })
        await dialog.vm.$emit('click:positive', new MouseEvent('click'))
        await flushPromises()

        expect($api.copayLists.download).toHaveBeenCalledTimes(1)
        expect($api.copayLists.download).toHaveBeenCalledWith({ billingId, form })
      })

      it('should not call $api.copayLists.download when the negative button is clicked', async () => {
        const isDivided = true

        await wrapper.vm.doAction()

        await setData(wrapper, {
          copayListDialog: {
            isDivided: ref(isDivided)
          }
        })

        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })
        await dialog.vm.$emit('click:negative', new MouseEvent('click'))
        await flushPromises()

        expect($api.copayLists.download).not.toHaveBeenCalled()
      })

      it('should download copay list PDF when job is successfully completed.', async () => {
        const job = createJobStub(token, JobStatus.success)
        mocked(execute).mockImplementationOnce(async ({ success }) => {
          await (success ?? noop)(job)
        })
        jest.spyOn($api.copayLists, 'download').mockResolvedValueOnce({ job })

        const isDivided = true

        await wrapper.vm.doAction()

        await setData(wrapper, {
          copayListDialog: {
            isDivided: ref(isDivided)
          }
        })

        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })
        await dialog.vm.$emit('click:positive', new MouseEvent('click'))
        await flushPromises()

        expect($download.uri).toHaveBeenCalledTimes(1)
        expect($download.uri).toHaveBeenCalledWith(job.data.uri, job.data.filename)
      })
    })
  })
})
