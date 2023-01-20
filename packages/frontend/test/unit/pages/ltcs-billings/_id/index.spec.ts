/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { LtcsBillingData, LtcsBillingStore, ltcsBillingStoreKey } from '~/composables/stores/use-ltcs-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsBillingsViewPage from '~/pages/ltcs-billings/_id/index.vue'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createLtcsBillingResponseStub } from '~~/stubs/create-ltcs-billing-response-stub'
import { createLtcsBillingStoreStub } from '~~/stubs/create-ltcs-billing-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-job-with-notification')

describe('pages/ltcs-billings/_id/index.vue', () => {
  const { shallowMount } = setupComponentTest()
  const responseStub = createLtcsBillingResponseStub()
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()

  let wrapper: Wrapper<Vue & any>
  let store: LtcsBillingStore

  type MountComponentParams = {
    auth?: Partial<Auth>
    options?: Partial<MountOptions<Vue>>
    storeData?: Partial<LtcsBillingData>
  }

  async function mountComponent ({ options, auth, storeData }: MountComponentParams = {}) {
    store = createLtcsBillingStoreStub({
      ...responseStub,
      ...(storeData ?? {}),
      billing: {
        ...responseStub.billing,
        status: LtcsBillingStatus.ready,
        ...(storeData?.billing ?? {})
      }
    })
    wrapper = shallowMount(LtcsBillingsViewPage, {
      ...(options ?? {}),
      ...provides(
        [ltcsBillingStoreKey, store],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      )
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useJobWithNotification).mockReturnValue({ execute })
  })

  it('should be rendered correctly', async () => {
    await mountComponent()
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

    it.each<string, LtcsBillingStatus>([
      ['checking', LtcsBillingStatus.checking],
      ['disabled', LtcsBillingStatus.disabled]
    ])('should not be rendered when the status is %s', (_, status) => {
      const billing = {
        ...responseStub.billing,
        status
      }
      const storeData = { billing }

      mountComponent({ storeData })

      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })

  describe('fix', () => {
    const billing = {
      ...responseStub.billing,
      status: LtcsBillingStatus.fixed
    }
    const storeData = { billing }
    const $snackbar = createMock<SnackbarService>()

    beforeAll(() => {
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mountComponent({ options: { mocks: { $snackbar } }, storeData })
      jest.spyOn(store, 'updateStatus').mockResolvedValue()
    })

    afterAll(() => {
      unmountComponent()
      jest.restoreAllMocks()
    })

    afterEach(() => {
      jest.clearAllMocks()
    })

    test('store.updateStatus を呼び出す', async () => {
      const { billing } = responseStub
      await wrapper.vm.fix()
      expect(store.updateStatus).toHaveBeenCalledTimes(1)
      expect(store.updateStatus).toHaveBeenCalledWith(billing.id, LtcsBillingStatus.fixed)
    })

    test('状態が更新された場合に snackbar にメッセージが表示される', async () => {
      await wrapper.vm.fix()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('請求の状態を変更しました。')
    })

    test('ネガティブボタンをクリックした場合 store.updateStatus が呼び出されないこと', async () => {
      jest.spyOn(store, 'updateStatus').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm.fix()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('請求の状態変更に失敗しました。')
    })
  })

  describe('disable', () => {
    const billing = {
      ...responseStub.billing,
      status: LtcsBillingStatus.disabled
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
      unmountComponent()
      jest.restoreAllMocks()
    })

    afterEach(() => {
      jest.clearAllMocks()
    })

    test('store.updateStatus を呼び出す', async () => {
      $confirm.show.mockResolvedValueOnce(true)
      const { billing } = responseStub
      await wrapper.vm.toDisable()
      expect(store.updateStatus).toHaveBeenCalledTimes(1)
      expect(store.updateStatus).toHaveBeenCalledWith(billing.id, LtcsBillingStatus.disabled)
    })

    test('状態が更新された場合に snackbar にメッセージが表示される', async () => {
      $confirm.show.mockResolvedValueOnce(true)
      await wrapper.vm.toDisable()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('請求の状態を変更しました。')
    })

    test('状態の更新に失敗した場合に snackbar にメッセージが表示される', async () => {
      $confirm.show.mockResolvedValueOnce(true)
      jest.spyOn(store, 'updateStatus').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm.toDisable()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('請求の状態変更に失敗しました。')
    })

    test('ネガティブボタンをクリックした場合 store.updateStatus が呼び出されないこと', async () => {
      mocked($confirm.show).mockResolvedValueOnce(false)
      await wrapper.vm.toDisable()

      expect(store.updateStatus).not.toHaveBeenCalled()
    })
  })
})
