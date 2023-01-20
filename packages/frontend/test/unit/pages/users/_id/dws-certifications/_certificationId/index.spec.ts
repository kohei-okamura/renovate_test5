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
import { dwsCertificationStateKey } from '~/composables/stores/use-dws-certification-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsCertificationViewPage from '~/pages/users/_id/dws-certifications/_certificationId/index.vue'
import { AlertService } from '~/services/alert-service'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createDwsCertificationResponseStub } from '~~/stubs/create-dws-certification-response-stub'
import { createDwsCertificationStoreStub } from '~~/stubs/create-dws-certification-store-stub'
import { createDwsCertificationStub } from '~~/stubs/create-dws-certification-stub'
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

describe('pages/users/_id/dws-certifications/_certificationId/index.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('dwsCertifications', 'users')
  const $back = createMockedBack()
  const stub = createDwsCertificationStub()
  const dwsCertificationResponse = createDwsCertificationResponseStub(stub.id)
  const dwsCertificationStore = createDwsCertificationStoreStub(dwsCertificationResponse)
  const userResponse = createUserResponseStub(stub.userId)
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    wrapper = mount(DwsCertificationViewPage, {
      ...options,
      ...provides(
        [dwsCertificationStateKey, dwsCertificationStore.state],
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

  describe('deleteDwsCertification', () => {
    const $alert = createMock<AlertService>()
    const $confirm = createMock<ConfirmDialogService>()
    const $snackbar = createMock<SnackbarService>()
    const mocks = {
      $alert,
      $api,
      $back,
      $confirm,
      $snackbar
    }

    beforeAll(() => {
      mountComponent({ mocks })
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($api.dwsCertifications, 'delete').mockResolvedValue(undefined)
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($alert, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($alert.error).mockReset()
      mocked($snackbar.success).mockReset()
      mocked($confirm.show).mockReset()
      mocked($api.dwsCertifications.delete).mockReset()
      $back.mockReset()
    })

    it('should show confirm dialog', async () => {
      await wrapper.vm.deleteDwsCertification()

      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.critical,
        message: '受給者証情報を削除します。\n\n本当によろしいですか？',
        positive: '削除'
      })
    })

    it('should call $api.dwsCertifications.delete when confirmed', async () => {
      await wrapper.vm.deleteDwsCertification()

      expect($api.dwsCertifications.delete).toHaveBeenCalledTimes(1)
      expect($api.dwsCertifications.delete).toHaveBeenCalledWith({ id: stub.id, userId: stub.userId })
    })

    it('should not call $api.dwsCertifications.delete when not confirmed', async () => {
      mocked($confirm.show).mockResolvedValue(false)

      await wrapper.vm.deleteDwsCertification()

      expect($api.dwsCertifications.delete).not.toHaveBeenCalled()
    })

    it('should display snackbar when dwsCertification deleted', async () => {
      await wrapper.vm.deleteDwsCertification()

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('受給者証情報を削除しました。')
    })

    it('should not display snackbar when not confirmed', async () => {
      mocked($confirm.show).mockResolvedValue(false)

      await wrapper.vm.deleteDwsCertification()

      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should not display snackbar when failed to delete dwsCertification', async () => {
      mocked($api.dwsCertifications.delete).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))

      await wrapper.vm.deleteDwsCertification()

      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should call $back when dwsCertification deleted', async () => {
      await wrapper.vm.deleteDwsCertification()

      expect($back).toHaveBeenCalledTimes(1)
      expect($back).toHaveBeenCalledWith(`/users/${stub.userId}#dws`)
    })

    it('should not call $back when dwsCertification deleted', async () => {
      mocked($confirm.show).mockResolvedValue(false)

      await wrapper.vm.deleteDwsCertification()

      expect($back).not.toHaveBeenCalled()
    })

    it('should not call $back when failed to delete dwsCertification', async () => {
      mocked($api.dwsCertifications.delete).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))

      await wrapper.vm.deleteDwsCertification()

      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($back).not.toHaveBeenCalled()
    })

    it.each([
      [120, '2'],
      [160, '2.7'],
      [60160, '1,002.7']
    ])('should be convert minutes unit to hours unites when use convertMinutesToHours function', (originMinutes, expectedHours) => {
      expect(wrapper.vm.convertMinutesToHours(originMinutes)).toEqual(expectedHours)
    })
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [
      Permission.updateDwsCertifications,
      Permission.deleteDwsCertifications
    ]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it.each([
      [requiredPermissions],
      ...requiredPermissions.map(x => [[x]])
    ])('should be rendered when the staff has permissions: %s', permissions => {
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })
})
