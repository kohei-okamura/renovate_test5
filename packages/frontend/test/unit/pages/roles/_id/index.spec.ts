/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { colors } from '~/colors'
import { permissionsStoreKey } from '~/composables/stores/use-permissions-store'
import { RoleData, roleStateKey } from '~/composables/stores/use-role-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import RolesViewPage from '~/pages/roles/_id/index.vue'
import { Plugins } from '~/plugins'
import { AlertService } from '~/services/alert-service'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createPermissionGroupStubs } from '~~/stubs/create-permission-group-stub'
import { createPermissionsStoreStub } from '~~/stubs/create-permissions-store-stub'
import { createRoleStoreStub } from '~~/stubs/create-role-store-stub'
import { createRoleStubs } from '~~/stubs/create-role-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/roles/_id/index.vue', () => {
  const { mount } = setupComponentTest()
  const $alert = createMock<AlertService>()
  const $api = createMockedApi('roles')
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
  const stubs = createRoleStubs()
  const stub = stubs[0]
  let wrapper: Wrapper<Vue & any>

  function mountComponent (
    data: RoleData = { role: stub },
    auth: Partial<Auth> = { isSystemAdmin: true }
  ) {
    const permissionGroups = createPermissionGroupStubs()
    const permissionsStore = createPermissionsStoreStub({ permissionGroups })
    const roleStore = createRoleStoreStub(data)
    wrapper = mount(RolesViewPage, {
      ...provides(
        [permissionsStoreKey, permissionsStore],
        [roleStateKey, roleStore.state],
        [sessionStoreKey, createAuthStub(auth)]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    jest.spyOn($alert, 'error').mockReturnValue()
    jest.spyOn($api.roles, 'delete').mockResolvedValue(undefined)
    jest.spyOn($confirm, 'show').mockResolvedValue(true)
    jest.spyOn($snackbar, 'success').mockReturnValue()
  })

  afterEach(() => {
    mocked($alert.error).mockReset()
    mocked($snackbar.success).mockReset()
    mocked($confirm.show).mockReset()
    mocked($api.roles.delete).mockReset()
    $back.mockReset()
  })

  it.each(stubs)('should be rendered correctly', role => {
    mountComponent({ role })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('deleteRole', () => {
    beforeEach(() => {
      mountComponent()
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should show confirm dialog', async () => {
      await wrapper.vm.deleteRole()
      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.critical,
        message: `ロール「${stub.name}」を削除します。\n\n本当によろしいですか？`,
        positive: '削除'
      })
    })

    it('should call $api.roles.delete when confirmed', async () => {
      await wrapper.vm.deleteRole()
      expect($api.roles.delete).toHaveBeenCalledTimes(1)
      expect($api.roles.delete).toHaveBeenCalledWith({ id: stub.id })
    })

    it('should not call $api.roles.delete when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteRole()
      expect($api.roles.delete).not.toHaveBeenCalled()
    })

    it('should display snackbar when role deleted', async () => {
      await wrapper.vm.deleteRole()
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('ロールを削除しました。')
    })

    it('should not display snackbar when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteRole()
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should not display snackbar when failed to delete role', async () => {
      jest.spyOn($api.roles, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.deleteRole()
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should call $back when role deleted', async () => {
      await wrapper.vm.deleteRole()
      expect($back).toHaveBeenCalledTimes(1)
      expect($back).toHaveBeenCalledWith('/roles')
    })

    it('should not call $back when role deleted', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteRole()
      expect($back).not.toHaveBeenCalled()
    })

    it('should not call $back when failed to delete role', async () => {
      jest.spyOn($api.roles, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.deleteRole()
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($back).not.toHaveBeenCalled()
    })
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [
      Permission.updateRoles,
      Permission.deleteRoles
    ]
    const selector = '[data-fab]'

    afterEach(() => {
      unmountComponent()
    })

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement(selector)
      expect(wrapper.find(selector)).toMatchSnapshot()
    })

    it.each([
      [requiredPermissions],
      ...requiredPermissions.map(x => [[x]])
    ])('should be rendered when the staff has permissions: %s', permissions => {
      mountComponent({ role: stub }, { permissions })
      expect(wrapper).toContainElement(selector)
      expect(wrapper.find(selector)).toMatchSnapshot()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ role: stub }, { permissions })
      expect(wrapper).not.toContainElement(selector)
    })
  })
})
