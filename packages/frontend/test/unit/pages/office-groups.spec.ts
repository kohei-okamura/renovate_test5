/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import OfficeGroupsIndexPage from '~/pages/office-groups.vue'
import { Plugins } from '~/plugins'
import { AlertService } from '~/services/alert-service'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createOfficeGroupIndexResponseStub } from '~~/stubs/create-office-group-index-response-stub'
import { createOfficeGroupStub, createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'
import { createOfficeGroupsStoreStub } from '~~/stubs/create-office-groups-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/office-groups.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('officeGroups')
  const mocks: Partial<Plugins> = {
    $api
  }
  const officeGroups = createOfficeGroupStubs()
  const officeGroupsStore = createOfficeGroupsStoreStub({ officeGroups })

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    wrapper = mount(OfficeGroupsIndexPage, {
      mocks,
      ...options,
      ...provides([sessionStoreKey, createAuthStub(auth)])
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.clearAllMocks()
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
  })

  it('should call officeGroupsStore.getIndex', () => {
    mountComponent()
    expect(officeGroupsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(officeGroupsStore.getIndex).toHaveBeenCalledWith()
  })

  it('should call $api.officeGroups.sort when sortable-tree component emits input event', async () => {
    jest.spyOn($api.officeGroups, 'sort').mockResolvedValue(createOfficeGroupIndexResponseStub())
    await mountComponent()
    const sortableTree = wrapper.find('[data-sortable-tree]').vm as any
    sortableTree.onInput([]) // emit input event
    expect($api.officeGroups.sort).toHaveBeenCalledTimes(1)
    expect($api.officeGroups.sort).toHaveBeenCalledWith({ list: [] })
  })

  describe('deleteOfficeGroup', () => {
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
    const dummy = createOfficeGroupStub()!

    beforeEach(async () => {
      jest.spyOn($alert, 'error').mockReturnValue()
      jest.spyOn($api.officeGroups, 'delete').mockResolvedValue(undefined)
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      await mountComponent({ mocks })
      jest.clearAllMocks()
    })

    it('should show confirm dialog', async () => {
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        message: `事業所グループ「${dummy.name}」を削除します。\n\n本当によろしいですか？`,
        positive: '削除'
      })
    })

    it('should call $api.officeGroups.delete when confirmed', async () => {
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect($api.officeGroups.delete).toHaveBeenCalledTimes(1)
      expect($api.officeGroups.delete).toHaveBeenCalledWith({ id: dummy.id })
    })

    it('should not call $api.officeGroups.delete when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect($api.officeGroups.delete).not.toHaveBeenCalled()
    })

    it('should dispatch office-groups/getIndex when office-group deleted', async () => {
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect(officeGroupsStore.getIndex).toHaveBeenCalledTimes(1)
      expect(officeGroupsStore.getIndex).toHaveBeenCalledWith()
    })

    it('should dispatch office-groups/getIndex when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect(officeGroupsStore.getIndex).not.toHaveBeenCalled()
    })

    it('should dispatch office-groups/getIndex when failed to delete office-group', async () => {
      jest.spyOn($api.officeGroups, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect(officeGroupsStore.getIndex).not.toHaveBeenCalled()
    })

    it('should display snackbar when office deleted', async () => {
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('事業所グループを削除しました。')
    })

    it('should not display snackbar when not confirmed', async () => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should not display snackbar when failed to delete office-group', async () => {
      jest.spyOn($api.officeGroups, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.deleteOfficeGroup(dummy)
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
    })
  })

  describe('FAB', () => {
    const requiredPermissions: Permission[] = [Permission.createOfficeGroups]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
    })

    it(`should be rendered when the staff has permission: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement('[data-fab]')
    })

    it(`should not be rendered when the staff does not have permission: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-fab]')
    })
  })

  describe('edit button', () => {
    const requiredPermissions: Permission[] = [Permission.updateOfficeGroups]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-edit-btn]')
    })

    it(`should be rendered when the staff has permission: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement('[data-edit-btn]')
    })

    it(`should not be rendered when the staff does not have permission: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-edit-btn]')
    })
  })

  describe('menu button', () => {
    const requiredPermissions: Permission[] = [
      Permission.createOfficeGroups,
      Permission.deleteOfficeGroups
    ]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-menu-button]')
      expect(wrapper.find('[data-menu-button]')).toMatchSnapshot()
    })

    it.each([
      [requiredPermissions],
      ...requiredPermissions.map(x => [[x]])
    ])('should be rendered when the staff has permission(s): %s', permissions => {
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement('[data-menu-button]')
      expect(wrapper.find('[data-menu-button]')).toMatchSnapshot()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-menu-button]')
    })
  })

  describe('hasOfficeGroupsUpdatePermission', () => {
    const requiredPermissions: Permission[] = [Permission.updateOfficeGroups]

    it('should be true when session auth is system admin', () => {
      mountComponent()
      expect(wrapper.vm.hasOfficeGroupsUpdatePermission).toBeTrue()
    })

    it(`should be true when the staff has permission: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent({}, { permissions })
      expect(wrapper.vm.hasOfficeGroupsUpdatePermission).toBeTrue()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper.vm.hasOfficeGroupsUpdatePermission).toBeFalse()
    })
  })
})
