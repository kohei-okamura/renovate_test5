/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import { MountOptions, Stubs, Wrapper } from '@vue/test-utils'
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import {
  DwsBillingStatementCopayCoordinationStatus
} from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { isEmpty, noop } from '@zinger/helpers/index'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import deepmerge from 'deepmerge'
import Vue from 'vue'
import {
  DwsBillingStatementData,
  dwsBillingStatementStateKey,
  DwsBillingStatementStore,
  dwsBillingStatementStoreKey
} from '~/composables/stores/use-dws-billing-statement-store'
import { dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsBillingStatementViewPage from '~/pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/index.vue'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsBillingBundleStub } from '~~/stubs/create-dws-billing-bundle-stub'
import { createDwsBillingOfficeStub } from '~~/stubs/create-dws-billing-office-stub'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createDwsBillingStatementResponseStub } from '~~/stubs/create-dws-billing-statement-response-stub'
import { createDwsBillingStatementStoreStub } from '~~/stubs/create-dws-billing-statement-store-stub'
import { createDwsBillingStatementStub } from '~~/stubs/create-dws-billing-statement-stub'
import { createDwsBillingStoreStub } from '~~/stubs/create-dws-billing-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/index.vue', () => {
  // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
  Element.prototype.scrollIntoView = noop

  const { mount, shallowMount } = setupComponentTest()
  const dwsBillingStore = createDwsBillingStoreStub(createDwsBillingResponseStub())
  const bundle = createDwsBillingBundleStub()
  const stub = createDwsBillingStatementStub({ bundle })
  const responseStub = createDwsBillingStatementResponseStub({ id: stub.id })
  const $route = createMockedRoute({ hash: '' })
  const $form = createMockedFormService()
  const $snackbar = createMock<SnackbarService>()

  let wrapper: Wrapper<Vue & any>
  let store: DwsBillingStatementStore

  type MountParameters = {
    options?: MountOptions<Vue>
    auth?: Partial<Auth>
    storeData?: DeepPartial<DwsBillingStatementData>
    isShallow?: true
  }

  function mountComponent ({
    options,
    auth,
    storeData,
    isShallow
  }: MountParameters = {}) {
    const data = deepmerge(responseStub, storeData ?? {}) as Partial<DwsBillingStatementData>
    store = createDwsBillingStatementStoreStub(data)
    const mocks = {
      $route,
      $form,
      ...options?.mocks
    }
    const stubs: Stubs = {
      'z-dws-billing-copay-coordination-form-dialog': true,
      'z-form-card-item': true,
      ...options?.stubs
    }
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(DwsBillingStatementViewPage, {
      ...options,
      ...provides(
        [dwsBillingStoreKey, dwsBillingStore],
        [dwsBillingStatementStoreKey, store],
        [dwsBillingStatementStateKey, store.state],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      ),
      mocks,
      stubs
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.clearAllMocks()
  })

  describe('initial display', () => {
    const statement = {
      copayCoordinationStatus: DwsBillingStatementCopayCoordinationStatus.unfilled
    }

    it('should be rendered correctly', () => {
      mountComponent()
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })

    it('should not call scrollIntoView if $route.hash does not include "copayCoordination"', () => {
      const spy = jest.spyOn(Element.prototype, 'scrollIntoView')
      const $route = createMockedRoute({ hash: '' })
      mountComponent({
        options: { mocks: { $route }, stubs: { 'z-data-card': false } },
        isShallow: true
      })
      expect(spy).not.toBeCalled()
      unmountComponent()
      spy.mockRestore()
    })

    it('should call scrollIntoView if $route.hash includes "copayCoordination"', () => {
      const spy = jest.spyOn(Element.prototype, 'scrollIntoView')
      const $route = createMockedRoute({ hash: '#copayCoordination' })
      mountComponent({
        options: { mocks: { $route }, stubs: { 'z-data-card': false } },
        isShallow: true
      })
      expect(spy).toBeCalledTimes(1)
      unmountComponent()
      spy.mockRestore()
    })

    describe('when user is admin and statement is checking', () => {
      beforeAll(() => {
        const storeData = {
          statement: { ...statement, status: DwsBillingStatus.checking }
        }
        mountComponent({ auth: { isSystemAdmin: true }, storeData })
      })

      afterAll(() => {
        unmountComponent()
      })

      it.each([
        ['edit copay coordination button', 'editCopayCoordinationButton'],
        ['copay coordination form', 'copayCoordinationForm']
      ])('should be rendered %s if statement can be updated', (_, ref) => {
        expect(wrapper.findComponent({ ref })).toExist()
      })

      it.each([
        ['determine button', 'determineButton'],
        ['remand button', 'remandButton']
      ])('should not be rendered %s if statement is checking', () => {
        expect(wrapper.findComponent({ ref: 'determineButton' })).not.toExist()
      })
    })

    describe('when user has permissions and statement is ready', () => {
      beforeAll(() => {
        const storeData = {
          statement: { ...statement, status: DwsBillingStatus.ready }
        }
        mountComponent({ auth: { permissions: [Permission.updateBillings] }, storeData })
      })

      afterAll(() => {
        unmountComponent()
      })

      it.each([
        ['edit copay coordination button', 'editCopayCoordinationButton'],
        ['copay coordination form', 'copayCoordinationForm']
      ])('should be rendered %s if login user has permission', (_, ref) => {
        expect(wrapper.findComponent({ ref })).toExist()
      })

      it('should be rendered determine button if statement is ready', () => {
        expect(wrapper.findComponent({ ref: 'determineButton' })).toExist()
      })
    })

    describe('when user has permissions and billing status is fixed or not', () => {
      afterAll(() => {
        unmountComponent()
      })

      it('should be rendered update status button if billing status is not fixed', () => {
        const storeData = {
          statement: { ...statement, status: DwsBillingStatus.ready },
          billing: { status: DwsBillingStatus.ready }
        }
        mountComponent({ auth: { permissions: [Permission.updateBillings] }, storeData })
        expect(wrapper.find('[data-update-status]')).toExist()
      })

      it('should not be rendered update status button if billing status is fixed', () => {
        const storeData = {
          statement: { ...statement, status: DwsBillingStatus.ready },
          billing: { status: DwsBillingStatus.fixed }
        }
        mountComponent({ auth: { permissions: [Permission.updateBillings] }, storeData })
        expect(wrapper.find('[data-update-status]')).not.toExist()
      })
    })

    describe('when user does not have permissions and statement is checking', () => {
      beforeAll(() => {
        const storeData = {
          statement: { ...statement, status: DwsBillingStatus.checking }
        }
        mountComponent({ auth: {}, storeData })
      })

      afterAll(() => {
        unmountComponent()
      })

      it.each([
        ['determine button', 'determineButton'],
        ['remand button', 'remandButton'],
        ['edit copay coordination button', 'editCopayCoordinationButton'],
        ['save button', 'saveButton'],
        ['managed copay input', 'managedCopay_0'],
        ['copay coordination form', 'copayCoordinationForm']
      ])('should not be rendered %s if login user does not have permission', (_, ref) => {
        expect(wrapper.findComponent({ ref })).not.toExist()
      })
    })

    describe('when user has permissions and statement is fixed', () => {
      beforeAll(() => {
        const storeData = {
          statement: { ...statement, status: DwsBillingStatus.fixed }
        }
        mountComponent({ auth: { isSystemAdmin: true }, storeData })
      })

      afterAll(() => {
        unmountComponent()
      })

      it.each([
        ['determine button', 'determineButton'],
        ['edit copay coordination button', 'editCopayCoordinationButton'],
        ['save button', 'saveButton'],
        ['managed copay input', 'managedCopay_0'],
        ['copay coordination form', 'copayCoordinationForm']
      ])('should not be rendered %s if statement is fixed', (_, ref) => {
        expect(wrapper.findComponent({ ref })).not.toExist()
      })
    })
  })

  describe('update statement', () => {
    const { billing, bundle, statement } = responseStub
    const aggregates = statement.aggregates
      .map((
        {
          serviceDivisionCode,
          managedCopay,
          subtotalSubsidy
        }
      ) => ({ serviceDivisionCode, managedCopay, subtotalSubsidy }))
      .sort((a, b) => {
        return parseInt(a.serviceDivisionCode) - parseInt(b.serviceDivisionCode)
      })
    const formValue = reactive(Object.fromEntries(aggregates.map(x => {
      return [
        x.serviceDivisionCode,
        {
          managedCopay: String(x.managedCopay),
          subtotalSubsidy: isEmpty(x.subtotalSubsidy) ? undefined : String(x.subtotalSubsidy)
        }
      ]
    })))
    beforeAll(() => {
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mountComponent({
        options: { mocks: { $snackbar } },
        storeData: { statement: { status: DwsBillingStatus.ready } }
      })
    })

    afterAll(() => {
      unmountComponent()
      jest.restoreAllMocks()
    })

    it('should call dwsBillingStatementStore.update', async () => {
      jest.spyOn(store, 'update').mockResolvedValue()
      const params: DwsBillingStatementsApi.UpdateParams = {
        billingId: billing.id,
        bundleId: bundle.id,
        id: statement.id,
        form: { aggregates }
      }
      await wrapper.vm.submit(formValue)
      expect(store.update).toHaveBeenCalledTimes(1)
      expect(store.update).toHaveBeenCalledWith(params)
      mocked(store.update).mockRestore()
    })

    it('should display success snackbar when succeed to update statement', async () => {
      jest.spyOn(store, 'update').mockResolvedValue()
      await wrapper.vm.submit(formValue)
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('明細書を編集しました。')
      mocked(store.update).mockRestore()
    })

    it('should display error snackbar when failed to update statement', async () => {
      jest.spyOn(store, 'update').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm.submit()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('明細書の編集に失敗しました。')
      mocked(store.update).mockRestore()
    })
  })

  describe.each([
    ['determine', DwsBillingStatus.fixed],
    ['remand', DwsBillingStatus.ready]
  ])('%s', (feature, status) => {
    beforeAll(() => {
      mountComponent({
        options: { mocks: { $snackbar } },
        storeData: { statement: { status: DwsBillingStatus.ready } }
      })
      jest.spyOn(dwsBillingStore, 'get')
      jest.spyOn(store, 'updateStatus').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked(dwsBillingStore.get).mockRestore()
      mocked(store.updateStatus).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      unmountComponent()
    })

    it('should call dwsBillingStatementStore.updateStatus', async () => {
      const { billing, bundle, statement } = responseStub
      const params: DwsBillingStatementsApi.UpdateStatusParams = {
        billingId: billing.id,
        bundleId: bundle.id,
        id: statement.id,
        form: { status }
      }
      await wrapper.vm[`${feature}`]()
      expect(store.updateStatus).toHaveBeenCalledTimes(1)
      expect(store.updateStatus).toHaveBeenCalledWith(params)
      expect(dwsBillingStore.get).toHaveBeenCalledTimes(1)
    })

    it('should display success snackbar when succeed to update status', async () => {
      await wrapper.vm[`${feature}`]()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('明細の状態を変更しました。')
    })

    it('should display error snackbar when failed to update status', async () => {
      jest.spyOn(store, 'updateStatus').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
      await wrapper.vm[`${feature}`]()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('明細の状態変更に失敗しました。')
    })
  })

  describe('copay coordination', () => {
    const statement = {
      status: DwsBillingStatus.ready
    }

    describe('initial display', () => {
      it('should be rendered correctly when copay coordination is not necessary', () => {
        const storeData = {
          statement: { ...statement, copayCoordinationStatus: DwsBillingStatementCopayCoordinationStatus.unapplicable }
        }
        mountComponent({ storeData })
        expect(wrapper.findComponent({ ref: 'copayCoordinationCard' })).toMatchSnapshot()
        unmountComponent()
      })

      it('should be rendered correctly when copay coordination is not created', () => {
        const storeData = {
          statement: { ...statement, copayCoordinationStatus: DwsBillingStatementCopayCoordinationStatus.uncreated }
        }
        mountComponent({ storeData })
        expect(wrapper.findComponent({ ref: 'copayCoordinationCard' })).toMatchSnapshot()
        unmountComponent()
      })

      it('should be rendered correctly when copay coordination is already created', () => {
        const storeData = {
          statement: {
            ...statement,
            copayCoordinationStatus: DwsBillingStatementCopayCoordinationStatus.fulfilled,
            copayCoordination: {
              office: createDwsBillingOfficeStub(),
              result: CopayCoordinationResult.coordinated,
              amount: 20000
            }
          }
        }
        mountComponent({ storeData })
        expect(wrapper.findComponent({ ref: 'copayCoordinationCard' })).toMatchSnapshot()
        unmountComponent()
      })
    })

    describe.each<[string, DwsBillingStatementCopayCoordinationStatus, string]>([
      ['register', DwsBillingStatementCopayCoordinationStatus.unfilled, '登録'],
      ['update', DwsBillingStatementCopayCoordinationStatus.fulfilled, '編集']
    ])('%s', (command, status, action) => {
      const { billing, bundle, statement } = responseStub
      const form: Partial<DwsBillingStatementsApi.UpdateCopayCoordinationForm> =
        status === DwsBillingStatementCopayCoordinationStatus.fulfilled
          ? {
            result: CopayCoordinationResult.coordinated,
            amount: 2840
          }
          : {
            result: undefined,
            amount: undefined
          }

      beforeEach(() => {
        jest.spyOn($snackbar, 'success').mockReturnValue()
        jest.spyOn($snackbar, 'error').mockReturnValue()
        mountComponent({
          isShallow: true,
          options: { mocks: { $snackbar } },
          storeData: { statement: { copayCoordinationStatus: status } }
        })
      })

      afterEach(() => {
        unmountComponent()
        jest.restoreAllMocks()
      })

      describe('dws-billing-copay-coordination form', () => {
        it('should not be displayed until call copayCoordinationEditor.openDialog', () => {
          const formWrapper = wrapper.findComponent({ ref: 'copayCoordinationForm' })
          expect(formWrapper.attributes()).not.toHaveProperty('dialog')
        })

        it('should be displayed after call copayCoordinationEditor.openDialog', async () => {
          const formWrapper = wrapper.findComponent({ ref: 'copayCoordinationForm' })
          await wrapper.vm.copayCoordinationEditor.openDialog()
          expect(formWrapper.attributes().dialog).toBe('true')
        })

        it('should call dwsBillingStatementStore.update when submit event emitted', async () => {
          jest.spyOn(store, 'updateCopayCoordination').mockResolvedValue()
          const params: DwsBillingStatementsApi.UpdateCopayCoordinationParams = {
            billingId: billing.id,
            bundleId: bundle.id,
            id: statement.id,
            form
          }
          const formWrapper = wrapper.findComponent({ ref: 'copayCoordinationForm' })
          expect(store.updateCopayCoordination).not.toHaveBeenCalled()

          await formWrapper.vm.$emit('submit', form)

          expect(store.updateCopayCoordination).toHaveBeenCalledTimes(1)
          expect(store.updateCopayCoordination).toHaveBeenCalledWith(params)
          mocked(store.updateCopayCoordination).mockRestore()
        })
      })

      it(`should display success snackbar when succeed to ${command} copay coordination`, async () => {
        jest.spyOn(store, 'updateCopayCoordination').mockResolvedValue()
        await wrapper.vm.copayCoordinationEditor.submit(form)
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith(`利用者負担上限額管理結果を${action}しました。`)
        mocked(store.updateCopayCoordination).mockRestore()
      })

      it(`should display error snackbar when failed to ${command} copay coordination`, async () => {
        jest.spyOn(store, 'updateCopayCoordination').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
        await wrapper.vm.copayCoordinationEditor.submit(form)
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith(`利用者負担上限額管理結果の${action}に失敗しました。`)
        mocked(store.updateCopayCoordination).mockRestore()
      })
    })
  })
})
