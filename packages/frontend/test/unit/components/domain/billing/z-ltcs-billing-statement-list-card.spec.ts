/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { noop } from 'lodash'
import Vue from 'vue'
import ZLtcsBillingStatementListCard from '~/components/domain/billing/z-ltcs-billing-statement-list-card.vue'
import { LtcsBillingStore, ltcsBillingStoreKey } from '~/composables/stores/use-ltcs-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { LtcsBillingStatement } from '~/models/ltcs-billing-statement'
import { LtcsBillingStatementsApi } from '~/services/api/ltcs-billing-statements-api'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createLtcsBillingResponseStub } from '~~/stubs/create-ltcs-billing-response-stub'
import { createLtcsBillingStatementStub } from '~~/stubs/create-ltcs-billing-statement-stub'
import { createLtcsBillingStoreStub } from '~~/stubs/create-ltcs-billing-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-job-polling')
jest.mock('~/composables/use-job-with-notification')

describe('z-ltcs-billing-statement-list-card.vue', () => {
  const { mount } = setupComponentTest()
  const { statements } = createLtcsBillingResponseStub()
  const $api = createMockedApi('ltcsBillingStatements')
  const $confirm = createMock<ConfirmDialogService>()
  const $form = createMockedFormService()
  const $snackbar = createMock<SnackbarService>()
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const responseStub = createLtcsBillingResponseStub()
  const items: LtcsBillingStatement[] = [
    createLtcsBillingStatementStub({ billingId: 1, bundleId: 2, id: 3 }),
    createLtcsBillingStatementStub({ billingId: 1, bundleId: 2, id: 4 })
  ]
  const providedIn = '2020-10'
  const billingStatus = LtcsBillingStatus.ready
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()

  let wrapper: Wrapper<Vue & any>
  let store: LtcsBillingStore

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
    store = createLtcsBillingStoreStub({
      ...responseStub,
      ...(storeData ?? {}),
      billing: {
        ...responseStub.billing,
        ...(storeData?.billing ?? {})
      }
    })
    wrapper = mount(ZLtcsBillingStatementListCard, {
      ...options,
      propsData: {
        billingStatus,
        items,
        providedIn,
        ...options.propsData
      },
      ...provides(
        [ltcsBillingStoreKey, store],
        [sessionStoreKey, createAuthStub({ isSystemAdmin: true })]
      ),
      mocks: {
        $api,
        $confirm,
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
      items: statements,
      providedIn: '2021-02'
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('checkbox', () => {
    afterEach(() => {
      unmountComponent()
    })
    it('should display checkbox if billingStatus is not fixed', function () {
      const propsData = {
        billingStatus: LtcsBillingStatus.ready
      }
      mountComponent({ propsData })
      expect(wrapper.find('.v-simple-checkbox')).toExist()
    })
    it('should not display checkbox if billingStatus is fixed', function () {
      const propsData = {
        billingStatus: LtcsBillingStatus.fixed
      }
      mountComponent({ propsData })
      expect(wrapper.find('.v-simple-checkbox')).not.toExist()
    })
  })

  // TODO: 検索による絞り込みをテストする

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
      jest.spyOn($api.ltcsBillingStatements, 'bulkUpdateStatus').mockResolvedValue({ job })
      jest.spyOn($api.ltcsBillingStatements, 'refresh').mockResolvedValue({ job })
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mocked(startJobPolling).mockImplementation(async init => await init())
      mocked(useJobWithNotification).mockReturnValue({ execute })
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
      mocked(startJobPolling).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($confirm.show).mockRestore()
      mocked($api.ltcsBillingStatements.bulkUpdateStatus).mockRestore()
      mocked($api.ltcsBillingStatements.refresh).mockRestore()
      mocked(store.get).mockRestore()
    })

    beforeEach(() => {
      jest.spyOn(store, 'get').mockResolvedValue()
    })

    afterEach(() => {
      mocked(startJobPolling).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($confirm.show).mockClear()
      mocked($api.ltcsBillingStatements.bulkUpdateStatus).mockClear()
      mocked($api.ltcsBillingStatements.refresh).mockClear()
      mocked(store.get).mockClear()
      execute.mockClear()
    })

    describe('confirm statements', () => {
      beforeEach(() => {
        setData(wrapper, { action: 'confirm-statements', selected: items })
      })

      it('should call $api.ltcsBillingStatements.bulkUpdateStatus when the positive button is clicked', async () => {
        const billingId = items[0].billingId
        const bundleId = items[0].bundleId
        const form: LtcsBillingStatementsApi.BulkUpdateStatusForm = {
          ids: items.map(x => x.id),
          status: LtcsBillingStatus.fixed
        }

        jest.spyOn($confirm, 'show').mockResolvedValueOnce(true)

        await wrapper.vm.doAction()

        expect($api.ltcsBillingStatements.bulkUpdateStatus).toHaveBeenCalledTimes(1)
        expect($api.ltcsBillingStatements.bulkUpdateStatus).toHaveBeenCalledWith({ billingId, bundleId, form })
      })

      it('should not call $api.ltcsBillingStatements.bulkUpdateStatus when the negative button is clicked', async () => {
        jest.spyOn($confirm, 'show').mockResolvedValueOnce(false)

        await wrapper.vm.doAction()

        expect($api.ltcsBillingStatements.bulkUpdateStatus).not.toHaveBeenCalled()
      })

      it('should update ltcs billing store when the confirmation was succeeded', async () => {
        const billingId = items[0].billingId
        const job = createJobStub(token, JobStatus.success)

        jest.spyOn($api.ltcsBillingStatements, 'bulkUpdateStatus').mockResolvedValueOnce({ job })

        await wrapper.vm.doAction()

        expect(store.get).toHaveBeenCalledTimes(1)
        expect(store.get).toHaveBeenCalledWith({ id: billingId })
      })
    })

    describe('refresh statements', () => {
      beforeEach(() => {
        setData(wrapper, { action: 'refresh-statements', selected: items })
      })

      it('should call $api.ltcsBillingStatements.refresh when the positive button is clicked', async () => {
        mocked(execute).mockImplementation(async ({ process }) => {
          await process()
        })

        const billingId = items[0].billingId
        const form: LtcsBillingStatementsApi.RefreshForm = {
          ids: items.map(x => x.id)
        }

        jest.spyOn($confirm, 'show').mockResolvedValueOnce(true)

        await wrapper.vm.doAction()

        expect($api.ltcsBillingStatements.refresh).toHaveBeenCalledTimes(1)
        expect($api.ltcsBillingStatements.refresh).toHaveBeenCalledWith({ billingId, form })
        mocked(execute).mockReset()
      })

      it('should not call $api.ltcsBillingStatements.refresh when the negative button is clicked', async () => {
        jest.spyOn($confirm, 'show').mockResolvedValueOnce(false)

        await wrapper.vm.doAction()

        expect($api.ltcsBillingStatements.refresh).not.toHaveBeenCalled()
      })

      it('should update ltcs billing store when the confirmation was succeeded', async () => {
        const job = createJobStub(token, JobStatus.success)
        mocked(execute).mockImplementation(async ({ success }) => {
          await (success ?? noop)(job)
        })

        const billingId = items[0].billingId

        jest.spyOn($api.ltcsBillingStatements, 'refresh').mockResolvedValueOnce({ job })

        await wrapper.vm.doAction()

        expect(store.get).toHaveBeenCalledTimes(1)
        expect(store.get).toHaveBeenCalledWith({ id: billingId })

        mocked(execute).mockReset()
      })
    })
  })
})
