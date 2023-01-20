/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { colors } from '~/colors'
import { ltcsSubsidyStateKey } from '~/composables/stores/use-ltcs-subsidy-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsSubsidyViewPage from '~/pages/users/_id/ltcs-subsidies/_ltcsSubsidyId/index.vue'
import { Plugins } from '~/plugins'
import { AlertService } from '~/services/alert-service'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createLtcsSubsidyResponseStub } from '~~/stubs/create-ltcs-subsidy-response-stub'
import { createLtcsSubsidyStoreStub } from '~~/stubs/create-ltcs-subsidy-store-stub'
import { createLtcsSubsidyStub } from '~~/stubs/create-ltcs-subsidy-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/ltcsSubsidies/_subsidyId/index.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsSubsidies', 'users')
  const stub = createLtcsSubsidyStub()
  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    auth?: Partial<Auth>
  }

  function mountComponent ({ auth, ...options }: MountComponentArguments = {}) {
    const ltcsSubsidyResponse = createLtcsSubsidyResponseStub(stub.id)
    const ltcsSubsidyStore = createLtcsSubsidyStoreStub(ltcsSubsidyResponse)
    const userResponse = createUserResponseStub(stub.userId)
    const userStore = createUserStoreStub(userResponse)
    wrapper = mount(LtcsSubsidyViewPage, () => ({
      ...options,
      ...provides(
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })],
        [ltcsSubsidyStateKey, ltcsSubsidyStore.state],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      )
    }))
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('deleteSubsidy', () => {
    const $alert = createMock<AlertService>()
    const $back = createMockedBack()
    const $confirm = createMock<ConfirmDialogService>()
    const $snackbar = createMock<SnackbarService>()
    const mocks: Partial<Plugins> = {
      $alert,
      $api,
      $back,
      $confirm,
      $snackbar
    }

    beforeAll(() => {
      jest.spyOn($alert, 'error').mockReturnValue()
      jest.spyOn($api.ltcsSubsidies, 'delete').mockResolvedValue(undefined)
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      mountComponent({ mocks })
    })

    afterAll(() => {
      unmountComponent()
      mocked($alert.error).mockRestore()
      mocked($api.ltcsSubsidies.delete).mockRestore()
      mocked($confirm.show).mockRestore()
      mocked($snackbar.success).mockRestore()
      $back.mockRestore()
    })

    afterEach(() => {
      mocked($alert.error).mockClear()
      mocked($api.ltcsSubsidies.delete).mockClear()
      mocked($confirm.show).mockClear()
      mocked($snackbar.success).mockClear()
      $back.mockClear()
    })

    it('should show confirm dialog', async () => {
      await wrapper.vm.deleteSubsidy()
      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.critical,
        message: '公費情報を削除します。\n\n本当によろしいですか？',
        positive: '削除'
      })
    })

    it('should call $api.ltcsSubsidies.delete when confirmed', async () => {
      await wrapper.vm.deleteSubsidy()
      expect($api.ltcsSubsidies.delete).toHaveBeenCalledTimes(1)
      expect($api.ltcsSubsidies.delete).toHaveBeenCalledWith({ id: stub.id, userId: stub.userId })
    })

    it('should not call $api.ltcsSubsidies.delete when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValueOnce(false)
      await wrapper.vm.deleteSubsidy()
      expect($api.ltcsSubsidies.delete).not.toHaveBeenCalled()
    })

    it('should display snackbar when subsidy deleted', async () => {
      await wrapper.vm.deleteSubsidy()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('公費情報を削除しました。')
    })

    it('should not display snackbar when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValueOnce(false)
      await wrapper.vm.deleteSubsidy()
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should not display snackbar when failed to delete subsidy', async () => {
      jest.spyOn($api.ltcsSubsidies, 'delete').mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.deleteSubsidy()
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should call $back when subsidy deleted', async () => {
      await wrapper.vm.deleteSubsidy()
      expect($back).toHaveBeenCalledTimes(1)
      expect($back).toHaveBeenCalledWith(`/users/${stub.userId}#ltcs`)
    })

    it('should not call $back when subsidy deleted', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValueOnce(false)
      await wrapper.vm.deleteSubsidy()
      expect($back).not.toHaveBeenCalled()
    })

    it('should not call $back when failed to delete subsidy', async () => {
      jest.spyOn($api.ltcsSubsidies, 'delete').mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.deleteSubsidy()
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($back).not.toHaveBeenCalled()
    })
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [
      Permission.updateUserLtcsSubsidies,
      Permission.deleteUserLtcsSubsidies
    ]
    const stubs = [
      'z-data-card title',
      'z-data-card-item',
      'z-era-date'
    ]

    it('should be rendered when session auth is system admin', () => {
      mountComponent({ stubs })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it.each([
      [requiredPermissions],
      ...requiredPermissions.map(x => [[x]])
    ])('should be rendered when the staff has permissions: %s', permissions => {
      mountComponent({ auth: { permissions }, stubs })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ auth: { permissions }, stubs })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })
})
