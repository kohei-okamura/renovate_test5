/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Stubs, Wrapper } from '@vue/test-utils'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { noop } from 'lodash'
import Vue from 'vue'
import { DwsBillingData, DwsBillingStore, dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsBillingViewPage from '~/pages/dws-billings/_id/index.vue'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createDwsBillingStoreStub } from '~~/stubs/create-dws-billing-store-stub'
import { createDwsBillingStub } from '~~/stubs/create-dws-billing-stub'
import { DWS_BILLING_ID_MIN } from '~~/stubs/create-dws-billing-stub-settings'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-job-with-notification')

describe('pages/dws-billings/_id/index.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const responseStub = createDwsBillingResponseStub(DWS_BILLING_ID_MIN, 3)
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()
  let wrapper: Wrapper<Vue & any>
  let store: DwsBillingStore

  type MountParameters = {
    auth?: Partial<Auth>
    isDeep?: true
    options?: MountOptions<Vue>
    storeData?: Partial<DwsBillingData>
  }

  function mountComponent ({ auth, isDeep, options, storeData }: MountParameters = {}) {
    const stubs: Stubs = {
      ...options?.stubs
    }
    store = createDwsBillingStoreStub(
      {
        ...responseStub,
        ...(storeData ?? {}),
        billing: {
          ...responseStub.billing,
          status: DwsBillingStatus.ready,
          ...(storeData?.billing ?? {})
        }
      },
      storeData?.job ? { job: storeData.job } : {}
    )
    const fn = isDeep ? mount : shallowMount
    wrapper = fn(DwsBillingViewPage, {
      ...options,
      ...provides(
        [dwsBillingStoreKey, store],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      ),
      stubs
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useJobWithNotification).mockReturnValue({ execute })
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent({ isDeep: true })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('FAB (speed dial)', () => {
    it('should be rendered when session auth is system admin', () => {
      mountComponent()

      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered when the staff has permissions', () => {
      const permissions = [Permission.updateBillings]
      const auth = { permissions }

      mountComponent({ auth })

      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it('should not be rendered when the staff does not have required permissions', () => {
      const permissions = Permission.values.filter(x => x !== Permission.updateBillings)
      const auth = { permissions }

      mountComponent({ auth })

      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })

  describe('fix', () => {
    const billing = {
      ...responseStub.billing,
      status: DwsBillingStatus.ready
    }
    const job = createJobStub('token', JobStatus.inProgress)
    const storeData = { billing, job }
    const $snackbar = createMock<SnackbarService>()

    beforeAll(() => {
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mountComponent({ options: { mocks: { $snackbar } }, storeData })
      jest.spyOn(store, 'updateStatus').mockResolvedValue()
    })

    afterAll(() => {
      mocked(store.updateStatus).mockRestore()
      unmountComponent()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
    })

    afterEach(() => {
      jest.clearAllMocks()
    })

    it('should call store.updateStatus', async () => {
      const { billing } = responseStub
      await wrapper.vm.speedDial.fix()
      expect(store.updateStatus).toHaveBeenCalledTimes(1)
      expect(store.updateStatus).toHaveBeenCalledWith(billing.id, DwsBillingStatus.fixed)
    })

    it('should display snackbar with success message when status updated', async () => {
      await wrapper.vm.speedDial.fix()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('請求の状態を変更しました。')
    })

    it('should display snackbar with error message when failed to update status', async () => {
      jest.spyOn(store, 'updateStatus').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm.speedDial.fix()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('請求の状態変更に失敗しました。')
    })

    it('should call execute if $api.dwsBillings.updateStatus return job', async () => {
      const jobWithNotification = useJobWithNotification()
      jest.spyOn(jobWithNotification, 'execute').mockImplementation(async ({ process }) => {
        await process()
      })
      await wrapper.vm.speedDial.fix()

      expect(jobWithNotification.execute).toHaveBeenCalledTimes(1)
    })
  })

  describe('disable', () => {
    const billing = {
      ...responseStub.billing,
      status: DwsBillingStatus.disabled
    }
    const storeData = { billing }
    const $snackbar = createMock<SnackbarService>()
    const $confirm = createMock<ConfirmDialogService>()

    beforeAll(() => {
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mountComponent({ options: { mocks: { $confirm, $snackbar } }, storeData })
      jest.spyOn(store, 'updateStatus').mockResolvedValue()
    })

    afterAll(() => {
      mocked(store.updateStatus).mockRestore()
      unmountComponent()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
    })

    afterEach(() => {
      jest.clearAllMocks()
    })

    test('store.updateStatus を呼び出す', async () => {
      $confirm.show.mockResolvedValueOnce(true)
      const { billing } = responseStub
      await wrapper.vm.speedDial.disable()
      expect(store.updateStatus).toHaveBeenCalledTimes(1)
      expect(store.updateStatus).toHaveBeenCalledWith(billing.id, DwsBillingStatus.disabled)
    })

    test('状態が更新された場合に snackbar にメッセージが表示される', async () => {
      $confirm.show.mockResolvedValueOnce(true)
      await wrapper.vm.speedDial.disable()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('請求の状態を変更しました。')
    })

    test('状態の更新が失敗した場合 snackbar にメッセージが表示される', async () => {
      $confirm.show.mockResolvedValueOnce(true)
      jest.spyOn(store, 'updateStatus').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm.speedDial.disable()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('請求の状態変更に失敗しました。')
    })

    test('ネガティブボタンをクリックした場合 store.updateStatus が呼び出されないこと', async () => {
      mocked($confirm.show).mockResolvedValueOnce(false)
      await wrapper.vm.speedDial.disable()

      expect(store.updateStatus).not.toHaveBeenCalled()
    })
  })

  describe('copy', () => {
    const billing = {
      ...responseStub.billing
    }
    const storeData = { billing }

    const $api = createMockedApi('dwsBillings')
    const $confirm = createMock<ConfirmDialogService>()

    beforeAll(() => {
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($api.dwsBillings, 'copy').mockResolvedValue(createJobResponseStub('token', JobStatus.waiting))

      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })
      mountComponent({ options: { mocks: { $confirm, $api } }, storeData })
      jest.spyOn(store, 'get').mockResolvedValue()
    })

    beforeEach(() => {
      jest.clearAllMocks()
    })

    it('should not call $api.dwsBillings.copy if the negative button is clicked', async () => {
      mocked($confirm.show).mockResolvedValueOnce(false)
      await wrapper.vm.speedDial.copy()

      expect($api.dwsBillings.copy).not.toHaveBeenCalled()
    })

    it('should call $api.dwsBillings.copy if the positive button is clicked', async () => {
      mocked($confirm.show).mockResolvedValueOnce(true)
      await wrapper.vm.speedDial.copy()

      expect($api.dwsBillings.copy).toHaveBeenCalledTimes(1)
      expect($api.dwsBillings.copy).toHaveBeenCalledWith({ id: billing.id })
    })

    it('should update dws billing store when the process was succeeded', async () => {
      const job = createJobStub('token', JobStatus.success)
      mocked(execute).mockImplementation(async ({ success }) => {
        await (success ?? noop)({ ...job, data: { billing: createDwsBillingStub() } })
      })

      await wrapper.vm.speedDial.copy()

      expect(store.get).toHaveBeenCalledTimes(1)
      expect(store.get).toHaveBeenCalledWith({ id: billing.id })
    })
  })
})
