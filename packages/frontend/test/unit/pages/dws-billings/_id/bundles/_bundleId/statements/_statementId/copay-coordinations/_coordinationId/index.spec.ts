/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import {
  DwsBillingCopayCoordinationState,
  dwsBillingCopayCoordinationStateKey,
  dwsBillingCopayCoordinationStoreKey
} from '~/composables/stores/use-dws-billing-copay-coordination-store'
import { dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsBillingCopayCoordinationViewPage
  from '~/pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/copay-coordinations/_coordinationId/index.vue'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { DownloadService } from '~/services/download-service'
import { SnackbarService } from '~/services/snackbar-service'
import {
  createDwsBillingCopayCoordinationResponseStub
} from '~~/stubs/create-dws-billing-copay-coordination-response-stub'
import { createDwsBillingCopayCoordinationStoreStub } from '~~/stubs/create-dws-billing-copay-coordination-store-stub'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createDwsBillingStoreStub } from '~~/stubs/create-dws-billing-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/copay-coordinations/_coordinationId/index.vue', () => {
  const { mount } = setupComponentTest()
  const response = createDwsBillingCopayCoordinationResponseStub()
  const store = createDwsBillingCopayCoordinationStoreStub(response)
  const state = store.state
  const dwsBillingStore = createDwsBillingStoreStub(createDwsBillingResponseStub())
  const $snackbar = createMock<SnackbarService>()
  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    auth?: Partial<Auth>
    storeData?: DwsBillingCopayCoordinationState
  }

  function mountComponent ({ auth, storeData, ...options }: MountComponentArguments = {}) {
    wrapper = mount(DwsBillingCopayCoordinationViewPage, {
      ...provides(
        [dwsBillingCopayCoordinationStateKey, { ...state, ...storeData }],
        [dwsBillingCopayCoordinationStoreKey, store],
        [dwsBillingStoreKey, dwsBillingStore],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      ),
      ...options
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('status button', () => {
    it('should rendered remand button when status is fixed', () => {
      const localStore = createDwsBillingCopayCoordinationStoreStub({
        ...response,
        copayCoordination: { ...response.copayCoordination, status: DwsBillingStatus.fixed }
      })
      mountComponent({
        storeData: {
          ...localStore.state
        }
      })
      expect(wrapper.find('[data-determine-btn]')).not.toExist()
      expect(wrapper.find('[data-remand-btn]')).toExist()
      unmountComponent()
    })
    it('should rendered remand button when status is ready', () => {
      const localStore = createDwsBillingCopayCoordinationStoreStub({
        ...response,
        copayCoordination: { ...response.copayCoordination, status: DwsBillingStatus.ready }
      })
      mountComponent({
        storeData: {
          ...localStore.state
        }
      })
      expect(wrapper.find('[data-determine-btn]')).toExist()
      expect(wrapper.find('[data-remand-btn]')).not.toExist()
      unmountComponent()
    })
  })

  describe.each([
    ['determine', DwsBillingStatus.fixed],
    ['remand', DwsBillingStatus.ready]
  ])('%s', (feature, status) => {
    beforeAll(() => {
      mountComponent({ mocks: { $snackbar } })
      jest.spyOn(store, 'get')
      jest.spyOn(dwsBillingStore, 'get')
      jest.spyOn(store, 'updateStatus').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked(store.get).mockRestore()
      mocked(dwsBillingStore.get).mockRestore()
      mocked(store.updateStatus).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      unmountComponent()
    })

    it('should call dwsBillingStatementStore.updateStatus', async () => {
      const { billing, bundle, copayCoordination } = response
      const params: DwsBillingStatementsApi.UpdateStatusParams = {
        billingId: billing.id,
        bundleId: bundle.id,
        id: copayCoordination.id,
        form: { status }
      }
      await wrapper.vm[`${feature}`]()
      expect(store.updateStatus).toHaveBeenCalledTimes(1)
      expect(store.updateStatus).toHaveBeenCalledWith(params)
      expect(dwsBillingStore.get).toHaveBeenCalledTimes(1)
    })

    it('should display success snackbar when succeed to update status', async () => {
      await wrapper.vm[`${feature}`]()
      expect($snackbar.success).toHaveBeenCalled()
      expect($snackbar.success).toHaveBeenCalledWith('利用者負担上限額管理結果票の状態を変更しました。')
    })

    it('should display error snackbar when failed to update status', async () => {
      jest.spyOn(store, 'updateStatus').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm[`${feature}`]()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('利用者負担上限額管理結果票の状態変更に失敗しました。')
    })
  })

  describe('speed dial', () => {
    const stubs = ['v-simple-table']
    it('should be rendered when session auth is system admin', () => {
      mountComponent({ stubs })
      expect(wrapper).toContainElement('[data-speed-dial]')
      unmountComponent()
    })

    it('should be rendered when the staff has updateBillings permission(s)', () => {
      const params = { auth: { permissions: [Permission.updateBillings] }, stubs }
      mountComponent(params)
      expect(wrapper).toContainElement('[data-speed-dial]')
      unmountComponent()
    })

    it('should be rendered when the staff has downloadBillings permission(s)', () => {
      const params = { auth: { permissions: [Permission.downloadBillings] }, stubs }
      mountComponent(params)
      expect(wrapper).toContainElement('[data-speed-dial]')
      unmountComponent()
    })

    it('should not be rendered when the staff does not have either permissions', () => {
      const permissions = Permission.values.filter(x => {
        return x !== Permission.downloadBillings && x !== Permission.updateBillings
      })
      const params = { auth: { permissions }, stubs }
      mountComponent(params)
      expect(wrapper).not.toContainElement('[data-speed-button]')
      unmountComponent()
    })

    it('should not be rendered when the status is fixed', () => {
      const localStore = createDwsBillingCopayCoordinationStoreStub({
        ...response,
        copayCoordination: { ...response.copayCoordination, status: DwsBillingStatus.fixed }
      })
      mountComponent({
        storeData: {
          ...localStore.state
        },
        stubs
      })
      expect(wrapper).not.toContainElement('[data-speed-button]')
      unmountComponent()
    })

    describe('edit button', () => {
      const requiredPermissions: Permission[] = [
        Permission.updateBillings
      ]

      it('should be rendered when session auth is system admin', () => {
        mountComponent({ stubs })
        expect(wrapper).toContainElement('[data-edit-button]')
        unmountComponent()
      })

      it('should be rendered when the staff has permission(s)', () => {
        const params = { auth: { permissions: requiredPermissions }, stubs }
        mountComponent(params)
        expect(wrapper).toContainElement('[data-edit-button]')
        unmountComponent()
      })

      it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
        const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
        const params = { auth: { permissions }, stubs }
        mountComponent(params)
        expect(wrapper).not.toContainElement('[data-edit-button]')
        unmountComponent()
      })
    })

    describe('download button', () => {
      const requiredPermissions: Permission[] = [
        Permission.downloadBillings
      ]

      it('should be rendered when session auth is system admin', () => {
        mountComponent({ stubs })
        expect(wrapper).toContainElement('[data-download-button]')
        unmountComponent()
      })

      it('should be rendered when the staff has permission(s)', () => {
        const params = { auth: { permissions: requiredPermissions }, stubs }
        mountComponent(params)
        expect(wrapper).toContainElement('[data-download-button]')
        unmountComponent()
      })

      it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
        const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
        const params = { auth: { permissions }, stubs }
        mountComponent(params)
        expect(wrapper).not.toContainElement('[data-download-button]')
        unmountComponent()
      })

      it('should download copay coordination PDF when download button is clicked', () => {
        const $download = createMock<DownloadService>()
        const mocks = { $download }
        mountComponent({ stubs, mocks })
        jest.spyOn($download, 'uri').mockResolvedValue()
        const downloadButton = wrapper.find('[data-download-button]')
        downloadButton.vm.$emit('click')
        expect($download.uri).toHaveBeenCalledTimes(1)
        const billingId = store.state.billing.value!.id
        const bundleId = store.state.bundle.value!.id
        const id = store.state.copayCoordination.value!.id
        expect($download.uri).toHaveBeenCalledWith(
          `/api/dws-billings/${billingId}/bundles/${bundleId}/copay-coordinations/${id}.pdf`
        )
        unmountComponent()
      })
    })
  })
})
