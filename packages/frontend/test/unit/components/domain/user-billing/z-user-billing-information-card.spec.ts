/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { colors } from '~/colors'
import UserBillingInformationCard from '~/components/domain/user-billing/z-user-billing-information-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userBillingStoreKey } from '~/composables/stores/use-user-billing-store'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { Auth } from '~/models/auth'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { UserBillingUser } from '~/models/user-billing-user'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createUserBillingStoreStub } from '~~/stubs/create-user-billing-store-stub'
import { createUserBillingStub } from '~~/stubs/create-user-billing-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-job-polling')

describe('z-user-billing-information-card.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const $api = createMockedApi('userBillings')
  const $snackbar = createMock<SnackbarService>()
  const $confirm = createMock<ConfirmDialogService>()
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const testYearMonth = TEST_NOW.toFormat(ISO_MONTH_FORMAT)
  const mocks = {
    $api,
    $confirm,
    $form,
    $snackbar
  }

  const userBilling = createUserBillingStub()
  const userBillingStore = createUserBillingStoreStub()

  const defaultPropsData = {
    userBilling: {
      ...userBilling,
      result: UserBillingResult.pending as UserBillingResult
    }
  }

  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    auth?: Partial<Auth>
    propsData?: Partial<typeof defaultPropsData>
  }

  function mountComponent ({ auth, propsData, ...options }: MountComponentArguments = {}) {
    mocked(useJobPolling).mockReturnValue({
      cancelJobPolling,
      startJobPolling
    })
    wrapper = mount(UserBillingInformationCard, {
      propsData: {
        ...defaultPropsData,
        ...propsData
      },
      ...options,
      mocks: {
        ...mocks,
        ...options?.mocks
      },
      stubs: ['z-date-confirm-dialog'],
      ...provides(
        [userBillingStoreKey, userBillingStore],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      )
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  describe('initial display', () => {
    it('should be rendered correctly when result is pending', () => {
      mountComponent()
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered correctly when result is paid', () => {
      mountComponent({ propsData: { userBilling: { ...userBilling, result: UserBillingResult.paid } } })
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered correctly when payment method is withdrawal', () => {
      const newUser: UserBillingUser = {
        ...userBilling.user,
        billingDestination: {
          ...userBilling.user.billingDestination,
          paymentMethod: PaymentMethod.withdrawal
        }
      }
      mountComponent({ propsData: { userBilling: { ...userBilling, user: newUser } } })
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })
  })

  describe('useAction', () => {
    const token = '10'
    const ids = [userBilling.id]

    beforeAll(() => {
      const job = createJobStub(token, JobStatus.waiting)
      jest.spyOn($api.userBillings, 'depositCancellation').mockResolvedValue({ job })
      jest.spyOn($api.userBillings, 'depositRegistration').mockResolvedValue({ job })
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'warning').mockReturnValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn(userBillingStore, 'get')
      mocked(startJobPolling).mockImplementation(async init => await init())
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
      mocked(startJobPolling).mockRestore()
      mocked(userBillingStore.get).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.warning).mockRestore()
      mocked($confirm.show).mockRestore()
      mocked($api.userBillings.depositCancellation).mockRestore()
      mocked($api.userBillings.depositRegistration).mockRestore()
    })

    afterEach(() => {
      mocked(startJobPolling).mockClear()
      mocked(userBillingStore.get).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($snackbar.warning).mockClear()
      mocked($confirm.show).mockClear()
      mocked($api.userBillings.depositCancellation).mockClear()
      mocked($api.userBillings.depositRegistration).mockClear()
    })

    describe('register deposit date', () => {
      it('should show cancel dialog', async () => {
        const dialog = wrapper.findComponent({ name: 'z-date-confirm-dialog' })
        expect(dialog.props().active).toBeFalse()

        await wrapper.vm.registration()
        await wrapper.vm.$nextTick()

        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it('should call $api.userBillings.depositRegistration when positive clicked', async () => {
        const form = { ids, depositedOn: testYearMonth }

        await wrapper.vm.registration()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-date-confirm-dialog' })

        await dialog.vm.$emit('click:positive', testYearMonth)

        expect($api.userBillings.depositRegistration).toHaveBeenCalledTimes(1)
        expect($api.userBillings.depositRegistration).toHaveBeenCalledWith({ form })
      })

      it('should not call $api.userBillings.depositRegistration when negative clicked', async () => {
        await wrapper.vm.registration()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-date-confirm-dialog' })

        await dialog.vm.$emit('click:negative')

        expect($api.userBillings.depositRegistration).not.toHaveBeenCalled()
      })

      it('should display snackbar when registration was successful', async () => {
        const job = createJobStub(token, JobStatus.success)
        jest.spyOn($api.userBillings, 'depositRegistration').mockResolvedValueOnce({ job })

        await wrapper.vm.dateRegistrationDialog.run(testYearMonth)

        expect(userBillingStore.get).toHaveBeenCalledTimes(1)
        expect(userBillingStore.get).toHaveBeenCalledWith({ id: userBilling.id })
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('入金日を登録しました。')
      })

      it('should display snackbar when registration was failure', async () => {
        const job = createJobStub(token, JobStatus.failure)
        jest.spyOn($api.userBillings, 'depositRegistration').mockResolvedValueOnce({ job })

        await wrapper.vm.dateRegistrationDialog.run(testYearMonth)

        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('入金日の登録に失敗しました。')
      })

      it('should display snackbar if failed to refresh screen', async () => {
        const job = createJobStub(token, JobStatus.success)
        jest.spyOn($api.userBillings, 'depositRegistration').mockResolvedValueOnce({ job })
        jest.spyOn(userBillingStore, 'get').mockRejectedValueOnce({})

        await wrapper.vm.dateRegistrationDialog.run(testYearMonth)

        expect($snackbar.warning).toHaveBeenCalledTimes(1)
        expect($snackbar.warning).toHaveBeenCalledWith('画面情報を更新できませんでした。最新の情報を見るにはブラウザをリロードしてください。')
      })
    })

    describe('delete deposit date', () => {
      beforeAll(() => {
        setData(wrapper, { userBilling: { ...wrapper.vm.userBilling, result: UserBillingResult.paid } })
      })

      it('should display confirmation dialog', async () => {
        mocked($confirm.show).mockResolvedValueOnce(false)

        await wrapper.vm.cancellation()
        await wrapper.vm.$nextTick()

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          color: colors.critical,
          message: '入金日を削除します。\n\n本当によろしいですか？',
          positive: '削除'
        })
      })

      describe('not confirmed', () => {
        beforeEach(() => {
          mocked($confirm.show).mockResolvedValueOnce(false)
        })

        it('should not call any api when not confirmed', async () => {
          await wrapper.vm.cancellation()
          await wrapper.vm.$nextTick()

          expect($api.userBillings.depositCancellation).not.toHaveBeenCalled()
        })

        it('should not display snackbar when not confirmed', async () => {
          await wrapper.vm.cancellation()
          await wrapper.vm.$nextTick()

          expect($snackbar.success).not.toHaveBeenCalled()
        })
      })

      describe('confirmed', () => {
        it('should call $api.userBillings.depositCancellation when the action is confirm', async () => {
          await wrapper.vm.cancellation()
          await wrapper.vm.$nextTick()

          expect($api.userBillings.depositCancellation).toHaveBeenCalledTimes(1)
          expect($api.userBillings.depositCancellation).toHaveBeenCalledWith({ form: { ids } })
        })

        it('should display snackbar when cancellation was successful', async () => {
          const job = createJobStub(token, JobStatus.success)
          jest.spyOn($api.userBillings, 'depositCancellation').mockResolvedValueOnce({ job })

          await wrapper.vm.cancellation()
          await wrapper.vm.$nextTick()

          expect(userBillingStore.get).toHaveBeenCalledTimes(1)
          expect(userBillingStore.get).toHaveBeenCalledWith({ id: userBilling.id })
          expect($snackbar.success).toHaveBeenCalledTimes(1)
          expect($snackbar.success).toHaveBeenCalledWith('入金日を削除しました。')
        })

        it('should display snackbar when cancellation was failure', async () => {
          const job = createJobStub(token, JobStatus.failure)
          jest.spyOn($api.userBillings, 'depositCancellation').mockResolvedValueOnce({ job })

          await wrapper.vm.cancellation()
          await wrapper.vm.$nextTick()

          expect($snackbar.error).toHaveBeenCalledTimes(1)
          expect($snackbar.error).toHaveBeenCalledWith('入金日の削除に失敗しました。')
        })

        it('should display snackbar if failed to refresh screen', async () => {
          const job = createJobStub(token, JobStatus.success)
          jest.spyOn($api.userBillings, 'depositCancellation').mockResolvedValueOnce({ job })
          jest.spyOn(userBillingStore, 'get').mockRejectedValueOnce({})

          await wrapper.vm.cancellation()
          await wrapper.vm.$nextTick()

          expect($snackbar.warning).toHaveBeenCalledTimes(1)
          expect($snackbar.warning).toHaveBeenCalledWith('画面情報を更新できませんでした。最新の情報を見るにはブラウザをリロードしてください。')
        })
      })
    })
  })
})
