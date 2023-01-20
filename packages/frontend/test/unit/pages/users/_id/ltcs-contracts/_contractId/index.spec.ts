/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { colors } from '~/colors'
import {
  ltcsContractStateKey,
  LtcsContractStore,
  ltcsContractStoreKey
} from '~/composables/stores/use-ltcs-contract-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsContractViewPage from '~/pages/users/_id/ltcs-contracts/_contractId/index.vue'
import { AlertService } from '~/services/alert-service'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createContractResponseStub } from '~~/stubs/create-contract-response-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createLtcsContractStoreStub } from '~~/stubs/create-ltcs-contract-store-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/users/_id/ltcs-contracts/_contractId/index.vue', () => {
  const { mount } = setupComponentTest()
  const stub = createContractStub()
  const ltcsContractStore = createLtcsContractStoreStub({
    contract: {
      ...createContractResponseStub(stub.id).contract,
      status: ContractStatus.formal
    }
  })
  const userResponse = createUserResponseStub(stub.userId)
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    options?: MountOptions<Vue>
    auth?: Partial<Auth>
    store?: LtcsContractStore
  }

  function mountComponent (params: MountComponentParams = {}) {
    const options = params.options ?? {}
    const auth = params.auth ?? { isSystemAdmin: true }
    const store = params.store ?? ltcsContractStore
    wrapper = mount(LtcsContractViewPage, {
      ...options,
      ...provides(
        [ltcsContractStoreKey, store],
        [ltcsContractStateKey, store.state],
        [sessionStoreKey, createAuthStub(auth)],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      )
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should be rendered expiredReason card item when contract status is terminated', function () {
    const contract = {
      ...stub,
      status: ContractStatus.terminated
    }
    const store = createLtcsContractStoreStub({ contract })
    mountComponent({ store })
    expect(wrapper).toContainElement('[data-expired-reason]')
    unmountComponent()
  })

  it('should not be rendered expiredReason card item when contract status is not terminated', function () {
    const contract = {
      ...stub,
      status: ContractStatus.formal
    }
    const store = createLtcsContractStoreStub({ contract })
    mountComponent({ store })
    expect(wrapper).not.toContainElement('[data-expired-reason]')
    unmountComponent()
  })

  /*
   * FIXME: contractStateの値が変更された時にパンくずリストが変更されるかを確認したい
   * createLtcsContractStoreStub()がundefinedを許容しないためスキップ
   * https://github.com/eustylelab/zinger/pull/816#discussion_r468354716
   */
  it.skip('should not show contract.status', function () {
    const contractState = createLtcsContractStoreStub(undefined)
    wrapper = mount(LtcsContractViewPage, {
      ...provides(
        [ltcsContractStateKey, contractState.state],
        [userStateKey, userStore.state]
      )
    })
    expect(wrapper.find('[breadcrumbs]').attributes('title')).toBe('-')
    unmountComponent()
  })

  describe('disableContract', () => {
    const $alert = createMock<AlertService>()
    const $back = createMockedBack()
    const $confirm = createMock<ConfirmDialogService>()
    const $snackbar = createMock<SnackbarService>()
    const mocks = {
      $alert,
      $back,
      $confirm,
      $snackbar
    }

    beforeEach(() => {
      jest.spyOn(ltcsContractStore, 'disable').mockResolvedValue()
      jest.spyOn($alert, 'error').mockReturnValue()
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      const options = { mocks }
      mountComponent({ options })
    })

    afterEach(() => {
      unmountComponent()
      mocked($snackbar.success).mockReset()
      mocked($confirm.show).mockReset()
      mocked($alert.error).mockReset()
      mocked(ltcsContractStore.disable).mockReset()
      $back.mockReset()
    })

    it('should show confirm dialog', async () => {
      await wrapper.vm.disableContract()
      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.critical,
        message: '契約を無効にします。\n\n本当によろしいですか？',
        positive: '実行'
      })
    })

    it('should call LtcsContractStore.disable when confirmed', async () => {
      await wrapper.vm.disableContract()

      expect(ltcsContractStore.disable).toHaveBeenCalledTimes(1)
      expect(ltcsContractStore.disable).toHaveBeenCalledWith({ id: stub.id, userId: stub.userId })
    })

    it('should not call LtcsContractStore.disable when not confirmed', async () => {
      mocked($confirm.show).mockResolvedValue(false)

      await wrapper.vm.disableContract()

      expect(ltcsContractStore.disable).not.toHaveBeenCalled()
    })

    it('should display snackbar when contract is disable', async () => {
      await wrapper.vm.disableContract()

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('契約を無効にしました。')
    })

    it('should not display snackbar when not confirmed', async () => {
      mocked($confirm.show).mockResolvedValue(false)

      await wrapper.vm.disableContract()

      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should not display snackbar when failed to disable contract', async () => {
      mocked(ltcsContractStore.disable).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))

      await wrapper.vm.disableContract()

      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should call $back when contract is disable', async () => {
      await wrapper.vm.disableContract()

      expect($back).toHaveBeenCalledTimes(1)
      expect($back).toHaveBeenCalledWith(`/users/${stub.userId}#ltcs`)
    })

    it('should not call $back when contract is disable', async () => {
      mocked($confirm.show).mockResolvedValue(false)

      await wrapper.vm.disableContract()

      expect($back).not.toHaveBeenCalled()
    })

    it('should not call $back when failed to disable contract', async () => {
      mocked(ltcsContractStore.disable).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))

      await wrapper.vm.disableContract()

      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($back).not.toHaveBeenCalled()
    })
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [Permission.updateLtcsContracts]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()

      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered when the staff has permissions', () => {
      const auth = {
        permissions: requiredPermissions
      }
      mountComponent({ auth })

      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }
      mountComponent({ auth })

      expect(wrapper).not.toContainElement('[data-fab]')
    })

    it('should not be rendered when contract is disable', () => {
      const contract = {
        ...stub,
        status: ContractStatus.disabled
      }
      const store = createLtcsContractStoreStub({ contract })

      mountComponent({ store })

      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })

    it('should not be rendered formal-button when contract is terminated', () => {
      const contract = {
        ...stub,
        status: ContractStatus.terminated
      }
      const store = createLtcsContractStoreStub({ contract })

      mountComponent({ store })

      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper).not.toContainElement('[data-fab-formal]')
      unmountComponent()
    })

    it.each([
      ['provisional', ContractStatus.provisional],
      ['formal', ContractStatus.formal]
    ])('should be rendered formal-button when contract is %s', (_, status) => {
      const contract = {
        ...stub,
        status
      }
      const store = createLtcsContractStoreStub({ contract })

      mountComponent({ store })

      expect(wrapper).toContainElement('[data-fab-formal]')
    })

    it('should not be rendered terminate-button when contract is provisional', () => {
      const status = ContractStatus.provisional
      const contract = { ...stub, status }
      const store = createLtcsContractStoreStub({ contract })

      mountComponent({ store })

      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper).not.toContainElement('[data-fab-terminate]')
      unmountComponent()
    })
  })
})
