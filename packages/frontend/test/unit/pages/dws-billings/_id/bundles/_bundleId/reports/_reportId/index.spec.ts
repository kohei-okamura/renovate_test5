/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import {
  DwsBillingServiceReportFormat,
  resolveDwsBillingServiceReportFormat
} from '@zinger/enums/lib/dws-billing-service-report-format'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import {
  dwsBillingServiceReportStateKey,
  DwsBillingServiceReportStore,
  dwsBillingServiceReportStoreKey
} from '~/composables/stores/use-dws-billing-service-report-store'
import { DwsBillingStore, dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsBillingServiceReportViewPage from '~/pages/dws-billings/_id/bundles/_bundleId/reports/_reportId/index.vue'
import { DwsBillingServiceReportsApi } from '~/services/api/dws-billing-service-reports-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createDwsBillingServiceReportResponseStub } from '~~/stubs/create-dws-billing-service-report-response-stub'
import { createDwsBillingServiceReportStoreStub } from '~~/stubs/create-dws-billing-service-report-store-stub'
import { createDwsBillingStoreStub } from '~~/stubs/create-dws-billing-store-stub'
import { DWS_BILLING_ID_MIN } from '~~/stubs/create-dws-billing-stub-settings'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/dws-billings/_id/bundles/_bundleId/reports/_reportId/index.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const dwsBillingStoreStub = createDwsBillingResponseStub(DWS_BILLING_ID_MIN, 1)
  dwsBillingStoreStub.billing = { ...dwsBillingStoreStub.billing, status: DwsBillingStatus.ready }
  const dwsBillingStore = createDwsBillingStoreStub(dwsBillingStoreStub)
  const response = createDwsBillingServiceReportResponseStub()
  const defaultStore = createDwsBillingServiceReportStoreStub(response)
  let wrapper: Wrapper<Vue & any>

  type MountParameters = {
    auth?: Partial<Auth>
    isShallow?: true
    options?: MountOptions<Vue>
    store?: DwsBillingServiceReportStore
    billingStore?: DwsBillingStore
  }

  function mountComponent ({
    auth,
    isShallow,
    options,
    store,
    billingStore
  }: MountParameters = {}) {
    const s = store ?? defaultStore
    const ds = billingStore ?? dwsBillingStore
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(DwsBillingServiceReportViewPage, {
      ...options,
      ...provides(
        [dwsBillingStoreKey, ds],
        [dwsBillingServiceReportStoreKey, s],
        [dwsBillingServiceReportStateKey, s.state],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      )
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  describe('initial display', () => {
    it('should be rendered correctly', () => {
      mountComponent()
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })

    describe.each([
      ['home help service', DwsBillingServiceReportFormat.homeHelpService, '居宅介護サービス提供実績記録票', 'formatOne'],
      ['visiting care for pwsd', DwsBillingServiceReportFormat.visitingCareForPwsd, '重度訪問介護サービス提供実績記録票', 'formatThreeOne']
    ])('should be rendered service report for "%s" correctly', (_, format, title, ref) => {
      const response = createDwsBillingServiceReportResponseStub()
      response.report = { ...response.report, format }
      const store = createDwsBillingServiceReportStoreStub(response)
      const options = {
        stubs: {
          'z-service-report-format-one': true,
          'z-service-report-format-three-one': true
        }
      }

      beforeAll(() => {
        mountComponent({ isShallow: true, options, store })
      })

      afterAll(() => {
        unmountComponent()
      })

      it(`should be rendered "${title}" in title`, () => {
        expect(wrapper.find('[data-service-report]').html()).toContain(title)
      })

      it(`should be used "${resolveDwsBillingServiceReportFormat(format)}" to the report format`, () => {
        expect(wrapper.findComponent({ ref })).toExist()
      })
    })
  })

  describe('report status button', () => {
    const localStore = (status: DwsBillingStatus) => {
      const response = createDwsBillingResponseStub()
      response.billing = { ...response.billing, status }
      return createDwsBillingStoreStub(response)
    }
    it('should be rendered when session auth is system admin', () => {
      mountComponent({ isShallow: true })
      expect(wrapper).toContainElement('[data-report-status-btn]')
      unmountComponent()
    })

    it('should be rendered when the staff has permission(s)', () => {
      const auth = { permissions: [Permission.updateBillings] }
      mountComponent({ auth, isShallow: true })
      expect(wrapper).toContainElement('[data-report-status-btn]')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${Permission.updateBillings}`, () => {
      const auth = {}
      mountComponent({ auth, isShallow: true })
      expect(wrapper).not.toContainElement('[data-report-status-btn]')
      unmountComponent()
    })

    it('should not be rendered when the billing status is fixed', () => {
      mountComponent({ isShallow: true, billingStore: localStore(DwsBillingStatus.fixed) })
      expect(wrapper).not.toContainElement('[data-report-status-btn]')
      unmountComponent()
    })

    it('should not be rendered when the billing status is disabled', () => {
      mountComponent({ isShallow: true, billingStore: localStore(DwsBillingStatus.disabled) })
      expect(wrapper).not.toContainElement('[data-report-status-btn]')
      unmountComponent()
    })
  })

  describe.each([
    ['determine', DwsBillingStatus.fixed],
    ['remand', DwsBillingStatus.ready]
  ])('%s', (feature, status) => {
    const $snackbar = createMock<SnackbarService>()
    const mocks = {
      $snackbar
    }

    beforeAll(() => {
      jest.spyOn(dwsBillingStore, 'get')
      jest.spyOn(defaultStore, 'updateStatus').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mountComponent({ isShallow: true, options: { mocks } })
    })

    afterAll(() => {
      unmountComponent()
      mocked(dwsBillingStore.get).mockRestore()
      mocked(defaultStore.updateStatus).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
    })

    afterEach(() => {
      mocked(dwsBillingStore.get).mockClear()
      mocked(defaultStore.updateStatus).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
    })

    it('should call dwsBillingServiceReportStore.updateStatus', async () => {
      const { billing, bundle, report } = response
      const params: DwsBillingServiceReportsApi.UpdateStatusParams = {
        billingId: billing.id,
        bundleId: bundle.id,
        id: report.id,
        form: { status }
      }
      await wrapper.vm[`${feature}`]()
      expect(defaultStore.updateStatus).toHaveBeenCalledTimes(1)
      expect(defaultStore.updateStatus).toHaveBeenCalledWith(params)
      expect(dwsBillingStore.get).toHaveBeenCalledTimes(1)
    })

    it('should display success snackbar when succeed to update status', async () => {
      await wrapper.vm[`${feature}`]()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('サービス提供実績記録票の状態を変更しました。')
    })

    it('should display error snackbar when failed to update status', async () => {
      jest.spyOn(defaultStore, 'updateStatus').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm[`${feature}`]()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('サービス提供実績記録票の状態変更に失敗しました。')
    })
  })
})
