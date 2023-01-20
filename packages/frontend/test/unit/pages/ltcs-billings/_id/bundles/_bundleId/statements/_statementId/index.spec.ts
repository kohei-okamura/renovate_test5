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
import {
  LtcsBillingStatementData,
  LtcsBillingStatementStore,
  ltcsBillingStatementStoreKey
} from '~/composables/stores/use-ltcs-billing-statement-store'
import { ltcsBillingStoreKey } from '~/composables/stores/use-ltcs-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsBillingStatementsViewPage
  from '~/pages/ltcs-billings/_id/bundles/_bundleId/statements/_statementId/index.vue'
import { SnackbarService } from '~/services/snackbar-service'
import { createLtcsBillingResponseStub } from '~~/stubs/create-ltcs-billing-response-stub'
import { createLtcsBillingStatementResponseStub } from '~~/stubs/create-ltcs-billing-statement-response-stub'
import { createLtcsBillingStatementStoreStub } from '~~/stubs/create-ltcs-billing-statement-store-stub'
import { createLtcsBillingStoreStub } from '~~/stubs/create-ltcs-billing-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/ltcs-billings/_id/bundles/_bundleId/statements/_statementId/index.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const ltcsBillingStore = createLtcsBillingStoreStub(createLtcsBillingResponseStub())
  const responseStub = createLtcsBillingStatementResponseStub()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }

  let wrapper: Wrapper<Vue & any>
  let store: LtcsBillingStatementStore

  type MountComponentParams = {
    auth?: Partial<Auth>
    isShallow?: true
    options?: Partial<MountOptions<Vue>>
    storeData?: DeepPartial<LtcsBillingStatementData>
  }

  async function mountComponent ({ auth, isShallow, options, storeData }: MountComponentParams = {}) {
    const statement = {
      ...responseStub.statement,
      status: LtcsBillingStatus.ready,
      ...(storeData?.statement ?? {})
    } as LtcsBillingStatementData['statement']
    const data = { ...responseStub, ...(storeData ?? {}) } as Partial<LtcsBillingStatementData>
    store = createLtcsBillingStatementStoreStub({ ...data, statement })
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(LtcsBillingStatementsViewPage, {
      ...(options ?? { mocks }),
      ...provides(
        [ltcsBillingStoreKey, ltcsBillingStore],
        [ltcsBillingStatementStoreKey, store],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      )
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('FAB (speed dial)', () => {
    it('should be rendered when session auth is system admin', () => {
      mountComponent({ isShallow: true })

      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered when the staff has permissions', () => {
      const permissions = [Permission.updateBillings]
      const auth = { permissions }

      mountComponent({ auth, isShallow: true })

      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it('should not be rendered when the staff does not have required permissions', () => {
      const permissions = Permission.values.filter(x => x !== Permission.updateBillings)
      const auth = { permissions }

      mountComponent({ auth, isShallow: true })

      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })

  it('should not be rendered when the status is checking', () => {
    const statement = {
      ...responseStub.statement,
      status: LtcsBillingStatus.checking
    }
    const storeData = { statement }

    mountComponent({ isShallow: true, storeData })

    expect(wrapper).not.toContainElement('[data-fab]')
    unmountComponent()
  })

  describe.each<string, LtcsBillingStatus>([
    ['fix', LtcsBillingStatus.fixed],
    ['unfix', LtcsBillingStatus.ready]
  ])('%s', (feature, status) => {
    const billing = {
      ...responseStub.billing,
      status
    }
    const storeData = { billing }

    beforeAll(() => {
      mountComponent({ isShallow: true, options: { mocks: { $snackbar, $form } }, storeData })
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn(ltcsBillingStore, 'get')
      jest.spyOn(store, 'updateStatus').mockResolvedValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked(ltcsBillingStore.get).mockRestore()
      mocked(store.updateStatus).mockRestore()
      unmountComponent()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked(ltcsBillingStore.get).mockClear()
      mocked(store.updateStatus).mockClear()
    })

    it('should call ltcsBillingStatementStore.updateStatus', async () => {
      const { billing, bundle, statement } = responseStub
      const params = {
        billingId: billing.id,
        bundleId: bundle.id,
        id: statement.id
      }
      await wrapper.vm[`${feature}`]()
      expect(store.updateStatus).toHaveBeenCalledTimes(1)
      expect(store.updateStatus).toHaveBeenCalledWith(params, status)
      expect(ltcsBillingStore.get).toHaveBeenCalledTimes(1)
    })

    it('should display success snackbar when succeed to update status', async () => {
      await wrapper.vm[`${feature}`]()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('請求の状態を変更しました。')
    })

    it('should display error snackbar when failed to update status', async () => {
      jest.spyOn(store, 'updateStatus').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm[`${feature}`]()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('請求の状態変更に失敗しました。')
    })
  })
})
