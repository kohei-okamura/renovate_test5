/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { colors } from '~/colors'
import { ltcsInsCardStateKey } from '~/composables/stores/use-ltcs-ins-card-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsInsCardViewPage from '~/pages/users/_id/ltcs-ins-cards/_cardId/index.vue'
import { Plugins } from '~/plugins'
import { AlertService } from '~/services/alert-service'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createLtcsInsCardResponseStub } from '~~/stubs/create-ltcs-ins-card-response-stub'
import { createLtcsInsCardStoreStub } from '~~/stubs/create-ltcs-ins-card-store-stub'
import { createLtcsInsCardStub } from '~~/stubs/create-ltcs-ins-card-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/users/_id/ltcs-ins-cards/_cardId/index.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsInsCards', 'users')
  const stub = createLtcsInsCardStub()

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    const ltcsInsCardResponse = createLtcsInsCardResponseStub(stub.id)
    const ltcsInsCardStore = createLtcsInsCardStoreStub(ltcsInsCardResponse)
    const userResponse = createUserResponseStub(stub.userId)
    const userStore = createUserStoreStub(userResponse)
    wrapper = mount(LtcsInsCardViewPage, () => ({
      ...options,
      ...provides(
        [ltcsInsCardStateKey, ltcsInsCardStore.state],
        [sessionStoreKey, createAuthStub(auth)],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
      )
    }))
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

  describe('deleteLtcsInsCard', () => {
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

    beforeEach(() => {
      jest.spyOn($alert, 'error').mockReturnValue()
      jest.spyOn($api.ltcsInsCards, 'delete').mockResolvedValue(undefined)
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      mountComponent({ mocks })
    })

    afterEach(() => {
      mocked($alert.error).mockReset()
      mocked($api.ltcsInsCards.delete).mockReset()
      mocked($confirm.show).mockReset()
      mocked($snackbar.success).mockReset()
      $back.mockReset()
      unmountComponent()
    })

    it('should show confirm dialog', async () => {
      await wrapper.vm.deleteLtcsInsCard()
      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.critical,
        message: '被保険者証情報を削除します。\n\n本当によろしいですか？',
        positive: '削除'
      })
    })

    it('should call $api.ltcsInsCards.delete when confirmed', async () => {
      await wrapper.vm.deleteLtcsInsCard()
      expect($api.ltcsInsCards.delete).toHaveBeenCalledTimes(1)
      expect($api.ltcsInsCards.delete).toHaveBeenCalledWith({ id: stub.id, userId: stub.userId })
    })

    it('should not call $api.ltcsInsCards.delete when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteLtcsInsCard()
      expect($api.ltcsInsCards.delete).not.toHaveBeenCalled()
    })

    it('should display snackbar when ltcsInsCard deleted', async () => {
      await wrapper.vm.deleteLtcsInsCard()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('被保険者証情報を削除しました。')
    })

    it('should not display snackbar when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteLtcsInsCard()
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should not display snackbar when failed to delete ltcsInsCard', async () => {
      jest.spyOn($api.ltcsInsCards, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.deleteLtcsInsCard()
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should call $back when ltcsInsCard deleted', async () => {
      await wrapper.vm.deleteLtcsInsCard()
      expect($back).toHaveBeenCalledTimes(1)
      expect($back).toHaveBeenCalledWith(`/users/${stub.userId}#ltcs`)
    })

    it('should not call $back when ltcsInsCard deleted', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteLtcsInsCard()
      expect($back).not.toHaveBeenCalled()
    })

    it('should not call $back when failed to delete ltcsInsCard', async () => {
      jest.spyOn($api.ltcsInsCards, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.deleteLtcsInsCard()
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($back).not.toHaveBeenCalled()
    })
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [
      Permission.updateLtcsInsCards,
      Permission.deleteLtcsInsCards
    ]

    afterEach(() => {
      unmountComponent()
    })

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
    })

    it.each([
      [requiredPermissions],
      ...requiredPermissions.map(x => [[x]])
    ])('should be rendered when the staff has permissions: %s', permissions => {
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-fab]')
    })
  })
})
