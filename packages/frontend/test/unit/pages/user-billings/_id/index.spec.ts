/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { MountOptions, Wrapper } from '@vue/test-utils'
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { Permission } from '@zinger/enums/lib/permission'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { camelToKebab } from '@zinger/helpers/index'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userBillingStateKey, UserBillingStore, userBillingStoreKey } from '~/composables/stores/use-user-billing-store'
import { useUserBillingFileDownloader } from '~/composables/use-user-billing-file-downloader'
import { Auth } from '~/models/auth'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { HttpStatusCode } from '~/models/http-status-code'
import { UserBilling, UserBillingId } from '~/models/user-billing'
import { UserBillingBankAccount } from '~/models/user-billing-bank-account'
import { UserBillingUser } from '~/models/user-billing-user'
import UserBillingViewPage from '~/pages/user-billings/_id/index.vue'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createUserBillingStoreStub } from '~~/stubs/create-user-billing-store-stub'
import { createUserBillingStub } from '~~/stubs/create-user-billing-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-user-billing-file-downloader')

describe('pages/user-billings/_id/index.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const userBilling = createUserBillingStub()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const downloader: ReturnType<typeof useUserBillingFileDownloader> = {
    downloadInvoices: jest.fn(),
    downloadNotices: jest.fn(),
    downloadReceipts: jest.fn(),
    downloadStatements: jest.fn(),
    errors: ref({}),
    progress: ref(false)
  }

  let wrapper: Wrapper<Vue & any>
  let store: UserBillingStore

  type MountComponentParams = {
    overwriteUserBilling?: Partial<UserBilling>
    auth?: Partial<Auth>
    isShallow?: true
    options?: Partial<MountOptions<Vue>>
  }

  async function mountComponent ({ overwriteUserBilling, auth, isShallow, options }: MountComponentParams = {}) {
    const state = { userBilling: { ...userBilling, result: UserBillingResult.paid, ...overwriteUserBilling } }
    store = createUserBillingStoreStub(state)
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(UserBillingViewPage, {
      ...options,
      mocks: {
        ...mocks,
        ...options?.mocks
      },
      stubs: ['z-date-confirm-dialog', 'z-prompt-dialog'],
      ...provides(
        [userBillingStoreKey, store],
        [userBillingStateKey, store.state],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      )
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useUserBillingFileDownloader).mockReturnValue(downloader)
  })

  afterAll(() => {
    mocked(useUserBillingFileDownloader).mockRestore()
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly when user has permission', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should be rendered correctly when user only has permission to view user billings', async () => {
    await mountComponent({ auth: { permissions: [Permission.viewUserBillings] } })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should be rendered correctly if the paymentMethod is withdrawal', async () => {
    const user: UserBillingUser = {
      ...userBilling.user,
      billingDestination: {
        ...userBilling.user.billingDestination,
        paymentMethod: PaymentMethod.withdrawal
      }
    }
    await mountComponent({ overwriteUserBilling: { user } })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should be exist bank account information button is correctly if userBillingResult is pending and having permission', async () => {
    const user: UserBillingUser = {
      ...userBilling.user,
      billingDestination: {
        ...userBilling.user.billingDestination,
        paymentMethod: PaymentMethod.withdrawal
      }
    }
    await mountComponent({
      auth: { permissions: [Permission.updateUserBillings] },
      overwriteUserBilling: { ...userBilling, result: UserBillingResult.pending, user }
    })

    expect(wrapper.find('[data-bank-account-information-button]')).toExist()

    unmountComponent()
  })

  describe('event', () => {
    type Params = { id: UserBillingId, form: UserBillingsApi.UpdateForm }

    beforeAll(() => {
      mountComponent({ isShallow: true, options: { mocks: { $snackbar, $form } } })
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn(store, 'get')
      jest.spyOn(store, 'update').mockResolvedValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked(store.get).mockRestore()
      mocked(store.update).mockRestore()
      unmountComponent()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked(store.get).mockClear()
      mocked(store.update).mockClear()
    })

    describe('update carried over amount', () => {
      const value = -3000

      it('should call userBillingStore.update', async () => {
        const params: Params = {
          id: userBilling.id,
          form: {
            carriedOverAmount: value,
            paymentMethod: userBilling.user.billingDestination.paymentMethod,
            bankAccount: userBilling.user.bankAccount
          }
        }
        await wrapper.vm.updateCarriedOverAmount(value)
        expect(store.update).toHaveBeenCalledTimes(1)
        expect(store.update).toHaveBeenCalledWith(params)
      })

      it('should display success snackbar when succeed to update', async () => {
        await wrapper.vm.updateCarriedOverAmount(value)
        await flushPromises()
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('繰越金額を変更しました。')
      })

      it('should display error snackbar when failed to update', async () => {
        jest.spyOn(store, 'update').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
        await wrapper.vm.updateCarriedOverAmount(value)
        await flushPromises()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('繰越金額の変更に失敗しました。')
      })
    })

    describe('update bank account', () => {
      const value: UserBillingBankAccount = {
        bankName: '三井住友',
        bankCode: '0009',
        bankBranchName: '九州',
        bankBranchCode: '731',
        bankAccountType: BankAccountType.fixedDeposit,
        bankAccountNumber: '4055475',
        bankAccountHolder: 'トクタ トモカ'
      }

      it('should call userBillingStore.update', async () => {
        const params: Params = {
          id: userBilling.id,
          form: {
            carriedOverAmount: userBilling.carriedOverAmount,
            paymentMethod: userBilling.user.billingDestination.paymentMethod,
            bankAccount: value
          }
        }
        await wrapper.vm.updateBankAccount(value)
        expect(store.update).toHaveBeenCalledTimes(1)
        expect(store.update).toHaveBeenCalledWith(params)
      })

      it('should display success snackbar when succeed to update', async () => {
        await wrapper.vm.updateBankAccount(value)
        await flushPromises()
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('銀行口座情報を変更しました。')
      })

      it('should display error snackbar when failed to update', async () => {
        jest.spyOn(store, 'update').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
        await wrapper.vm.updateBankAccount(value)
        await flushPromises()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('銀行口座情報の変更に失敗しました。')
      })
    })

    describe('update payment method', () => {
      const value = PaymentMethod.transfer

      it('should call userBillingStore.update', async () => {
        const params: Params = {
          id: userBilling.id,
          form: {
            carriedOverAmount: userBilling.carriedOverAmount,
            paymentMethod: value,
            bankAccount: userBilling.user.bankAccount
          }
        }
        await wrapper.vm.updatePaymentMethod(value)
        expect(store.update).toHaveBeenCalledTimes(1)
        expect(store.update).toHaveBeenCalledWith(params)
      })

      it('should display success snackbar when succeed to update', async () => {
        await wrapper.vm.updatePaymentMethod(value)
        await flushPromises()
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('支払方法を変更しました。')
      })

      it('should display error snackbar when failed to update', async () => {
        jest.spyOn(store, 'update').mockRejectedValueOnce(createAxiosError(HttpStatusCode.Forbidden))
        await wrapper.vm.updatePaymentMethod(value)
        await flushPromises()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('支払方法の変更に失敗しました。')
      })
    })
  })

  describe('download', () => {
    type AllDownloaderFunctions = Omit<ReturnType<typeof useUserBillingFileDownloader>, 'errors' | 'progress'>
    type ExtendedDownloaderFunctions = AllDownloaderFunctions

    it('should not rendered download statement button if both dwsItem and ltcsItem are undefined', async () => {
      await mountComponent({
        auth: { permissions: [Permission.viewUserBillings] },
        overwriteUserBilling: { dwsItem: undefined, ltcsItem: undefined }
      })

      expect(wrapper.find('[data-download-button="statement"]')).not.toExist()

      unmountComponent()
    })

    it('should not rendered download invoice button if result is none', async () => {
      await mountComponent({
        auth: { permissions: [Permission.viewUserBillings] },
        overwriteUserBilling: { result: UserBillingResult.none }
      })

      expect(wrapper.find('[data-download-button="invoice"]')).not.toExist()

      unmountComponent()
    })

    it('should not rendered download receipt button if result is not paid', async () => {
      await mountComponent({
        auth: { permissions: [Permission.viewUserBillings] },
        overwriteUserBilling: { result: UserBillingResult.pending }
      })

      expect(wrapper.find('[data-download-button="receipt"]')).not.toExist()

      unmountComponent()
    })

    it('should not rendered download notice button if dwsItem is undefined', async () => {
      await mountComponent({
        auth: { permissions: [Permission.viewUserBillings] },
        overwriteUserBilling: { dwsItem: undefined }
      })

      expect(wrapper.find('[data-download-button="statement"]')).toExist()
      expect(wrapper.find('[data-download-button="notice"]')).not.toExist()

      unmountComponent()
    })

    describe.each<string, keyof ExtendedDownloaderFunctions>([
      ['invoice', 'downloadInvoices'],
      ['receipt', 'downloadReceipts'],
      ['notice', 'downloadNotices'],
      ['statement', 'downloadStatements']
    ])('download %s', (type, fnName) => {
      const testYearMonth = TEST_NOW.toFormat(ISO_MONTH_FORMAT)

      beforeAll(() => {
        mountComponent()
      })

      afterAll(() => {
        unmountComponent()
      })

      afterEach(() => {
        mocked(useUserBillingFileDownloader).mockClear()
        mocked(downloader[fnName]).mockClear()
        downloader.errors.value = {}
      })

      it('should show date confirmation dialog', async () => {
        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        expect(dialog.props().active).toBeFalse()

        await wrapper.find(`[data-download-button="${type}"]`).vm.$emit('click')
        await wrapper.vm.$nextTick()

        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it(`should call useUserBillingFileDownloader.${fnName} when positive clicked`, async () => {
        await wrapper.find(`[data-download-button="${type}"]`).vm.$emit('click')
        await wrapper.vm.$nextTick()

        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        await dialog.vm.$emit('click:positive', testYearMonth)

        expect(downloader[fnName]).toHaveBeenCalledTimes(1)
        expect(downloader[fnName]).toHaveBeenCalledWith({ ids: [userBilling.id], issuedOn: testYearMonth })
      })

      it(`should not call useUserBillingFileDownloader.${fnName} when negative clicked`, async () => {
        await wrapper.find(`[data-download-button="${type}"]`).vm.$emit('click')
        await wrapper.vm.$nextTick()

        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        await dialog.vm.$emit('click:negative')

        expect(downloader[fnName]).not.toHaveBeenCalled()
      })

      it(`should display errors when error occurred in useUserBillingFileDownloader.${fnName}`, async () => {
        mocked(downloader[fnName]).mockImplementationOnce(() => {
          downloader.errors.value = { ids: ['不正なidが含まれています。'], issuedOn: ['発行日が不正です。'] }
          return Promise.resolve()
        })
        await wrapper.find(`[data-download-button="${type}"]`).vm.$emit('click')
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

  describe('submit', () => {
    beforeAll(() => {
      mountComponent()
      jest.spyOn(store, 'update').mockResolvedValue()
    })

    afterAll(() => {
      unmountComponent()
      mocked(store.update).mockRestore()
    })

    afterEach(() => {
      mocked(store.update).mockClear()
    })

    it.each([
      ['bankName', '銀行名を入力してください。'],
      ['bankCode', '銀行コードを入力してください。'],
      ['bankBranchName', '支店名を入力してください。'],
      ['bankBranchCode', '支店コードを入力してください。'],
      ['bankAccountType', '銀行口座種別を入力してください。'],
      ['bankAccountNumber', '口座番号を入力してください。'],
      ['bankAccountHolder', '口座名義を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(store.update)
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              ['bankAccount.' + key]: [message]
            }
          }))

        await wrapper.vm.updateBankAccount(userBilling.user.bankAccount)
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
